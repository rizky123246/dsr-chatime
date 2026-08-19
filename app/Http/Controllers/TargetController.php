<?php

namespace App\Http\Controllers;

use App\Models\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'site_code' => 'required',
            'month' => 'required',
            'target_sales' => 'required|numeric|min:0'
        ]);

        $year = date('Y', strtotime($request->month));
        $month = date('m', strtotime($request->month));

        Target::updateOrCreate(
            [
                'site_code' => $request->site_code,
                'year' => $year,
                'month' => $month,
            ],
            [
                'target_sales' => $request->target_sales,
                'created_by' => Auth::id() ?? 1
            ]
        );

        return back()->with('success', 'Target berhasil disimpan');
    }
}
