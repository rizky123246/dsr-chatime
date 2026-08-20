<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\GenerateLaporanService;
use App\Services\EnsureMasterDataService;


class UploadDataController extends Controller
{
    /**
     * Display upload data page
     */
    public function index()
    {
        return view('dashboard.upload-data');
    }

    /**
     * Import pembayaran data from CSV
     */
    public function importPembayaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $file = $request->file('csv_file');
        $fileName = $file->getClientOriginalName();
    
        $alreadyImported = Pembayaran::where('source_file', $fileName)->exists();
    
        if ($alreadyImported) {
            return response()->json([
                'success' => false,
                'message' => "File {$fileName} sudah pernah diupload"
            ], 422);
        }
    
        try {
            $filePath = $file->getRealPath();
            $fileHandle = fopen($filePath, 'r');
    
            if (!$fileHandle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to open file'
                ], 422);
            }
    
            // Skip header row
            fgetcsv($fileHandle, 1000, ',');
    
            $importedCount = 0;
            $errors = [];
            $rowNumber = 2;
    
            while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
                try {
                    if (count($row) < 6) {
                        $errors[] = "Row {$rowNumber}: Insufficient columns";
                        $rowNumber++;
                        continue;
                    }
    
                    $amount = $this->cleanAmount($row[0] ?? '');
                    $orderNo = trim($row[1] ?? '');
                    $receiptNo = trim($row[2] ?? '');
                    $invoiceReference = trim($row[3] ?? '');
                    $mopCode = trim($row[4] ?? '');
                    $mopeName = trim($row[5] ?? '');
    
                    Pembayaran::create([
                        'amount' => $amount,
                        'order_no' => $orderNo,
                        'receipt_no' => $receiptNo,
                        'invoice_reference' => $invoiceReference ?: null,
                        'mop_code' => $mopCode,
                        'mop_name' => $mopeName,
                        'source_file' => $fileName,
                    ]);
    
                    $importedCount++;
    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("Import pembayaran error at row {$rowNumber}: " . $e->getMessage());
                }
    
                $rowNumber++;
            }
    
            fclose($fileHandle);
    
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} pembayaran records",
                'imported_count' => $importedCount,
                'errors' => $errors,
                'total_rows' => $rowNumber - 2
            ]);
    
        } catch (\Exception $e) {
            Log::error('Pembayaran import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Import penjualan data from CSV (25 columns format)
     *
     * Sebelum insert ke penjualans, article_code & site_code dipastikan
     * ada di master data lebih dulu lewat EnsureMasterDataService.
     * Kalau belum ada, otomatis dibuatkan — jadi tidak perlu lagi
     * mematikan FOREIGN_KEY_CHECKS untuk "memaksa" insert lolos.
     */
    public function importPenjualan(
        Request $request,
        GenerateLaporanService $service,
        EnsureMasterDataService $ensureService
    ) {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('csv_file');
        $fileName = $file->getClientOriginalName();

        $alreadyImported = Penjualan::where('source_file', $fileName)->exists();

        if ($alreadyImported) {
            return response()->json([
                'success' => false,
                'message' => "File {$fileName} sudah pernah diupload"
            ], 422);
        }

        // Increase execution time for large files
        set_time_limit(300); // 5 minutes

        try {
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();
            $fileHandle = fopen($filePath, 'r');

            if (!$fileHandle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to open file'
                ], 422);
            }

            // Skip header row
            $header = fgetcsv($fileHandle, 1000, ',');
            Log::info("CSV Header for penjualan: " . json_encode($header));

            $importedCount = 0;
            $errors = [];
            $warnings = [];
            $rowNumber = 2; // Start from row 2 (after header)
            $batchSize = 100; // Process in batches
            $batch = [];

            // ===============================
            // LOAD MASTER PRODUCT (SEKALI SAJA, untuk cache lookup cepat)
            // ===============================
            $products = Product::all()
                ->mapWithKeys(function ($item) {
                    return [strtoupper(trim($item->article_code)) => $item];
                });

            $duplicateCount = 0;
            $newDataCount = 0;

            // balik pointer ke awal setelah header
            rewind($fileHandle);
            fgetcsv($fileHandle, 1000, ','); // skip header lagi

            $batch = [];
            $batchSize = 100;
            $dates = [];
            $sites = [];

            while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
                try {
                    // Debug: Log row count and content (only for first 10 rows)
                    if ($rowNumber <= 12) {
                        Log::info("Row {$rowNumber}: " . count($row) . " columns - " . json_encode($row));
                    }

                    // Validate row data - need at least 25 columns
                    if (count($row) < 25) {
                        $errors[] = "Row {$rowNumber}: Insufficient columns (found " . count($row) . ", need 25)";
                        $rowNumber++;
                        continue;
                    }

                    // Parse 25 columns according to your exact format
                    $createdDate = $this->parseDate($row[0] ?? '');
                    $createdTime = $this->parseTime($row[1] ?? '');
                    $orderNo = trim($row[2] ?? '');
                    $receiptNo = trim($row[3] ?? '');
                    $invoiceReference = trim($row[4] ?? '');
                    $void = $this->parseBoolean($row[5] ?? '');
                    $siteCode = trim($row[6] ?? '');
                    $siteDescription = trim($row[7] ?? '');
                    $articleCode = strtoupper(trim($row[8] ?? ''));
                    $articleName = trim($row[9] ?? '');
                    $quantity = $this->cleanInteger($row[10] ?? '');
                    $originalPrice = $this->cleanAmount($row[11] ?? '');
                    $netPrice = $this->cleanAmount($row[12] ?? '');
                    $promotionAmount = $this->cleanAmount($row[13] ?? '');
                    $promotionCode = trim($row[14] ?? '');
                    $promotionName = trim($row[15] ?? '');
                    $promotionChannel = trim($row[16] ?? ''); // Channel (bukan promotion_channel)
                    $departmentCode = trim($row[17] ?? '');
                    $departmentName = trim($row[18] ?? '');
                    $commodityCode = trim($row[19] ?? '');
                    $commodityName = trim($row[20] ?? '');
                    $merchandiseCode = trim($row[21] ?? '');
                    $merchandiseName = trim($row[22] ?? '');
                    $productGroupCode = trim($row[23] ?? '');
                    $productGroupName = trim($row[24] ?? '');

                    if ($createdDate && $siteCode) {
                        $dates[] = $createdDate;
                        $sites[$createdDate][] = $siteCode;
                    }

                    if (empty($articleCode)) {
                        $errors[] = "Row {$rowNumber}: Article code kosong";
                        $rowNumber++;
                        continue;
                    }

                    // ===============================
                    // PASTIKAN MASTER DATA ADA (auto-create kalau belum ada)
                    // ===============================
                    $effectiveSiteCode = $siteCode ?: 'CGI01';
                    $effectiveSiteDescription = $siteDescription ?: 'Chatime Grand Indonesia';

                    // Cek dari collection yang sudah di-preload dulu (hindari query per baris)
                    $product = $products[$articleCode] ?? null;

                    if (!$product) {
                        // Belum ada di master → auto-create lewat service, lalu simpan
                        // ke collection cache supaya baris berikutnya dengan article_code
                        // yang sama tidak insert ulang.
                        $product = $ensureService->ensureProduct($articleCode, $articleName);
                        $products[$articleCode] = $product;

                        $warnings[] = "Row {$rowNumber}: Product {$articleCode} belum ada di master → dibuatkan otomatis";
                    }

                    $size = $product->size ?? '-';
                    $type = $product->type ?? 'Unknown';

                    // Pastikan store/site juga ada di master data (auto-create kalau belum ada).
                    // ensureStore sudah punya cache internal sendiri, jadi aman dipanggil per baris.
                    $ensureService->ensureStore($effectiveSiteCode, $effectiveSiteDescription);

                    // Debug: Log parsed values (only for first 5 rows)
                    if ($rowNumber <= 7) {
                        Log::info("Row {$rowNumber} parsed: " . json_encode([
                            'createdDate' => $createdDate,
                            'createdTime' => $createdTime,
                            'orderNo' => $orderNo,
                            'receiptNo' => $receiptNo,
                            'articleName' => $articleName,
                            'quantity' => $quantity,
                            'netPrice' => $netPrice
                        ]));
                    }

                    // More flexible validation - allow net_price = 0 but warn about it
                    if (empty($createdDate) || empty($createdTime) ||
                        empty($articleName) || empty($quantity) || $quantity <= 0) {

                        $errors[] = "Row {$rowNumber}: Missing or invalid required fields. Found: Date={$createdDate}, Time={$createdTime}, Article={$articleName}, Qty={$quantity}, NetPrice={$netPrice}";
                        $rowNumber++;
                        continue;
                    }

                    $newDataCount++;

                    // Add to batch for bulk insert
                    $batch[] = [
                        'created_date' => $createdDate,
                        'created_time' => $createdTime,
                        'order_no' => $orderNo ?: 'ORD' . str_pad($importedCount + 1, 6, '0', STR_PAD_LEFT),
                        'receipt_no' => $receiptNo ?: 'RCP' . str_pad($importedCount + 1, 6, '0', STR_PAD_LEFT),
                        'invoice_reference' => $invoiceReference ?: null,
                        'void' => $void,
                        'site_code' => $effectiveSiteCode,
                        'site_description' => $effectiveSiteDescription,
                        'article_code' => $articleCode ?: $this->generateArticleCode($articleName),
                        'article_name' => $articleName,
                        'quantity' => $quantity,
                        'original_price' => $originalPrice ?: $netPrice,
                        'net_price' => $netPrice,
                        'promotion_amount' => $promotionAmount,
                        'promotion_code' => $promotionCode,
                        'promotion_name' => $promotionName,
                        'promotion_channel' => $promotionChannel,
                        'department_code' => $departmentCode,
                        'department_name' => $departmentName,
                        'commodity_code' => $commodityCode,
                        'commodity_name' => $commodityName,
                        'merchandise_code' => $merchandiseCode,
                        'merchandise_name' => $merchandiseName,
                        'product_group_code' => $productGroupCode,
                        'product_group_name' => $productGroupName,

                        'size' => $size,
                        'type' => $type,
                        'source_file' => $fileName,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $importedCount++;

                    // Insert batch when it reaches the batch size
                    if (count($batch) >= $batchSize) {
                        $this->insertBatch($batch);
                        $batch = [];

                        // Log progress every 1000 records
                        if ($importedCount % 1000 === 0) {
                            Log::info("Imported {$importedCount} records so far...");
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("Import penjualan error at row {$rowNumber}: " . $e->getMessage());
                }

                $rowNumber++;
            }

            // Insert remaining records in batch
            if (!empty($batch)) {
                $this->insertBatch($batch);
            }

            fclose($fileHandle);

            // Generate laporan untuk setiap kombinasi tanggal + site yang baru diimport
            $uniqueDates = array_unique($dates);

            foreach ($uniqueDates as $date) {
                $sitesForDate = Penjualan::whereDate('created_date', $date)
                    ->pluck('site_code')
                    ->unique();

                foreach ($sitesForDate as $site) {
                    $service->generate($date, $site);
                }
            }

            // ❗ Tidak ada satupun baris yang berhasil masuk ke batch.
            // Catatan: $duplicateCount memang selalu 0 (logic cek duplikat per baris
            // sudah tidak ada), jadi kalau newDataCount = 0 penyebabnya HAMPIR PASTI
            // baris gagal validasi (kolom < 25, field wajib kosong, qty <= 0),
            // bukan karena data duplikat. Makanya $errors ikut dikirim di sini
            // supaya kelihatan alasan sebenarnya per baris.
            if ($newDataCount === 0) {
                Log::warning('Penjualan import: 0 baris valid.', [
                    'file' => $fileName,
                    'total_rows' => $rowNumber - 2,
                    'error_count' => count($errors),
                    'sample_errors' => array_slice($errors, 0, 10),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada baris yang berhasil diimport. Cek detail error di bawah.',
                    'duplicate_count' => $duplicateCount,
                    'new_data_count' => $newDataCount,
                    'total_rows' => $rowNumber - 2,
                    'errors' => $errors,
                    'warnings' => $warnings,
                ], 422);
            }

            // ✅ kalau ada data baru
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} penjualan records",
                'imported_count' => $importedCount,
                'duplicate_count' => $duplicateCount,
                'new_data_count' => $newDataCount,
                'errors' => $errors,
                'warnings' => $warnings,
                'total_rows' => $rowNumber - 2
            ]);

        } catch (\Exception $e) {
            Log::error('Penjualan import error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Insert batch records for better performance
     */
    private function insertBatch($batch)
    {
        try {
            Penjualan::insert($batch);
        } catch (\Exception $e) {
            Log::error('Batch insert error: ' . $e->getMessage());

            // Fallback to individual inserts
            foreach ($batch as $record) {
                try {
                    Penjualan::create($record);
                } catch (\Exception $ex) {
                    Log::error('Individual insert error: ' . $ex->getMessage());
                }
            }
        }
    }

    /**
     * Download pembayaran template
     */
    public function downloadPembayaranTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pembayaran_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Amount',
                'Order No',
                'Receipt No',
                'Invoice Reference',
                'MOP Code',
                'MOP Name'
            ]);

            fputcsv($file, [
                50000,
                'ORD001',
                'RCP001',
                'INV001',
                'CASH',
                'Cash Payment'
            ]);

            fputcsv($file, [
                75000,
                'ORD002',
                'RCP002',
                'INV002',
                'OVO',
                'OVO E-Wallet'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download penjualan template (25 columns)
     */
    public function downloadPenjualanTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="penjualan_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Created Date',
                'Created Time',
                'Order No',
                'Receipt No',
                'Invoice Reference',
                'Void',
                'Site Code',
                'Site Description',
                'Article Code',
                'Article Name',
                'Quantity',
                'Original Price',
                'Net Price',
                'Promotion Amount',
                'Promotion Code',
                'Promotion Name',
                'Channel',
                'Department Code',
                'Department Name',
                'Commodity Code',
                'Commodity Name',
                'Merchandise Code',
                'Merchandise Name',
                'Product Group Code',
                'Product Group Name'
            ]);

            fputcsv($file, [
                '2024-01-15',
                '10:30:00',
                'ORD001',
                'RCP001',
                'INV001',
                'FALSE',
                'CGI01',
                'Chatime Grand Indonesia',
                'PMT001',
                'Pearl Milk Tea',
                2,
                25000,
                25000,
                0,
                '',
                '',
                'In-Store',
                'BEV001',
                'Beverages',
                'TEA001',
                'Tea Based',
                'MTC001',
                'Milk Tea Category',
                'PMT001',
                'Pearl Milk Tea'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper function to clean amount
     */
    private function cleanAmount($value)
    {
        $cleaned = preg_replace('/[^0-9,\.-]/', '', $value);
        $cleaned = str_replace(',', '', $cleaned);

        if (empty($cleaned) || !is_numeric($cleaned)) {
            return 0;
        }

        return (float) $cleaned;
    }

    /**
     * Helper function to clean integer
     */
    private function cleanInteger($value)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        if (empty($cleaned) || !is_numeric($cleaned)) {
            return 0;
        }

        return (int) $cleaned;
    }

    /**
     * Helper function to parse date
     */
    private function parseDate($value)
    {
        try {
            $value = trim($value);
            if (empty($value)) {
                return Carbon::now()->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Date parsing failed for value: {$value}");
            return Carbon::now()->format('Y-m-d');
        }
    }

    /**
     * Helper function to parse time
     */
    private function parseTime($value)
    {
        try {
            $value = trim($value);
            if (empty($value)) {
                return Carbon::now()->format('H:i:s');
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            Log::warning("Time parsing failed for value: {$value}");
            return Carbon::now()->format('H:i:s');
        }
    }

    /**
     * Helper function to parse boolean
     */
    private function parseBoolean($value)
    {
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'y', 't']);
    }

    /**
     * Helper function to generate article code
     */
    private function generateArticleCode($productName)
    {
        $words = explode(' ', strtolower($productName));
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 3));
        }
        return substr($code, 0, 6) ?: 'PROD001';
    }

    /**
     * Helper function to generate product group code
     */
    private function generateProductGroupCode($productName)
    {
        $words = explode(' ', strtolower($productName));
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 4));
        }
        return substr($code, 0, 8) ?: 'GROUP001';
    }

}