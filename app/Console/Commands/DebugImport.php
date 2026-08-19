<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Log;

class DebugImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:import {file=debug.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug CSV import process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        $this->info("Debugging CSV import for file: {$filePath}");
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }
        
        $fileHandle = fopen($filePath, 'r');
        if (!$fileHandle) {
            $this->error("Cannot open file: {$filePath}");
            return;
        }
        
        // Read header
        $header = fgetcsv($fileHandle, 1000, ',');
        $this->info("Header: " . json_encode($header));
        
        $importedCount = 0;
        $errors = [];
        $rowNumber = 2;
        
        while (($row = fgetcsv($fileHandle, 1000, ',')) !== false) {
            $this->line("\n=== Row {$rowNumber} ===");
            $this->line("Raw: " . json_encode($row));
            $this->line("Columns: " . count($row));
            
            try {
                // Validate row data - need at least 25 columns
                if (count($row) < 25) {
                    $error = "Row {$rowNumber}: Insufficient columns (found " . count($row) . ", need 25)";
                    $errors[] = $error;
                    $this->error($error);
                    $rowNumber++;
                    continue;
                }

                // Parse data (25 columns format)
                $createdDate = $row[0] ?? '';
                $createdTime = $row[1] ?? '';
                $orderNo = $row[2] ?? '';
                $receiptNo = $row[3] ?? '';
                $invoiceReference = $row[4] ?? '';
                $void = $row[5] ?? '';
                $siteCode = $row[6] ?? '';
                $siteDescription = $row[7] ?? '';
                $articleCode = $row[8] ?? '';
                $articleName = $row[9] ?? '';
                $quantity = preg_replace('/[^0-9]/', '', $row[10] ?? '');
                $originalPrice = preg_replace('/[^0-9.,]/', '', $row[11] ?? '');
                $netPrice = preg_replace('/[^0-9.,]/', '', $row[12] ?? '');
                $promotionAmount = preg_replace('/[^0-9.,]/', '', $row[13] ?? '');
                $promotionCode = $row[14] ?? '';
                $promotionName = $row[15] ?? '';
                $promotionChannel = $row[16] ?? ''; // Channel
                $departmentCode = $row[17] ?? '';
                $departmentName = $row[18] ?? '';
                $commodityCode = $row[19] ?? '';
                $commodityName = $row[20] ?? '';
                $merchandiseCode = $row[21] ?? '';
                $merchandiseName = $row[22] ?? '';
                $productGroupCode = $row[23] ?? '';
                $productGroupName = $row[24] ?? '';

                $this->line("Parsed (25 cols):");
                $this->line("  Created Date: '{$createdDate}'");
                $this->line("  Created Time: '{$createdTime}'");
                $this->line("  Order No: '{$orderNo}'");
                $this->line("  Receipt No: '{$receiptNo}'");
                $this->line("  Article Name: '{$articleName}'");
                $this->line("  Quantity: '{$quantity}'");
                $this->line("  Net Price: '{$netPrice}'");
                $this->line("  Channel: '{$promotionChannel}'");

                // Validate required fields
                $emptyFields = [];
                if (empty($createdDate)) $emptyFields[] = 'createdDate';
                if (empty($createdTime)) $emptyFields[] = 'createdTime';
                if (empty($articleName)) $emptyFields[] = 'articleName';
                if (empty($quantity) || $quantity <= 0) $emptyFields[] = 'quantity';
                if (empty($netPrice) || $netPrice <= 0) $emptyFields[] = 'netPrice';

                if (!empty($emptyFields)) {
                    $error = "Row {$rowNumber}: Missing/invalid fields: " . implode(', ', $emptyFields);
                    $errors[] = $error;
                    $this->error($error);
                    $rowNumber++;
                    continue;
                }

                $this->info("✓ Row would be imported successfully");
                $importedCount++;

                // Test only first 5 rows
                if ($rowNumber >= 6) break;

            } catch (\Exception $e) {
                $error = "Row {$rowNumber}: " . $e->getMessage();
                $errors[] = $error;
                $this->error($error);
            }
            
            $rowNumber++;
        }
        
        fclose($fileHandle);
        
        $this->info("\n=== SUMMARY ===");
        $this->info("Imported count: {$importedCount}");
        $this->info("Errors: " . count($errors));
        
        if (!empty($errors)) {
            $this->error("First few errors:");
            foreach (array_slice($errors, 0, 3) as $error) {
                $this->error("  - {$error}");
            }
        }
        
        // Check current database count
        $currentCount = Penjualan::count();
        $this->info("Current penjualan records in database: {$currentCount}");
    }
}
