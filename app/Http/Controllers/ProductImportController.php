<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportController extends Controller
{
    public function import(Request $request)
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
}
