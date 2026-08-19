<?php

namespace App\Http\Controllers;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class MasterUploadControllerFix extends Controller
{
    // HALAMAN VIEW
    public function index()
    {
        $stores = Store::where('is_active', 1)->get(); // atau sesuai field kamu

        return view('area-manager.upload-master', compact('stores'));
    }

    // HANDLE UPLOAD
    public function import(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'file' => 'required|mimes:xlsx,csv'
        ]);

        $type = $request->type;

        if ($type == 'product') {
            return $this->importProduct($request);
        }

        if ($type == 'store') {
            return $this->importStore($request);
        }

        if ($type == 'employee') {
            return $this->importEmployee($request);
        }

        return back()->with('error', 'Tipe tidak dikenali');
    }

    // =========================
    // IMPORT PRODUCT
    // =========================
    private function importProduct($request)
   
    {
        $file = $request->file('file');

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
        
            if (empty($row[0])) continue; // skip kalau kosong
        
            Product::updateOrCreate(
                ['article_code' => $row[0]], // unique
                [
                    'name'   => $row[1] ?? '',
                    'size'   => $row[2] ?? null,
                    'type'   => $row[3] ?? 'Drink',
                    'series' => $row[4] ?? null,
                    'brand'  => $row[5] ?? '',
                ]
            );
        }

        return back()->with('success', 'Data berhasil diimport!');
    }

    // =========================
    // IMPORT STORE
    // =========================
    private function importStore($request)
    { $file = $request->file('file');

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
    
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // skip header
    
            if (empty($row[0])) continue; // skip kosong
    
            Store::updateOrCreate(
                ['code' => $row[0]], // UNIQUE
                [
                    'name'      => $row[1] ?? '',
                    'city'      => $row[2] ?? '',
                    'is_active' => $row[3] ?? 1,
                ]
            );
        }
    
        return back()->with('success', 'Data Store berhasil diimport!');
    }

    // =========================
    // IMPORT EMPLOYEE
    // =========================
    private function importEmployee($request)
    {
        return back()->with('success', 'Upload Employee berhasil (dummy)');
    }
}