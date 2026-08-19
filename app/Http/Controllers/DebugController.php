<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function testImport()
    {
        try {
            Log::info('Test import endpoint called');
            
            // Test basic response
            return response()->json([
                'success' => true,
                'message' => 'Test endpoint working',
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Test endpoint error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test endpoint error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testUploadData()
    {
        try {
            Log::info('Test upload data controller');
            
            // Test if UploadDataController can be instantiated
            $controller = new \App\Http\Controllers\UploadDataController();
            
            return response()->json([
                'success' => true,
                'message' => 'UploadDataController loaded successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Test upload data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test upload data error: ' . $e->getMessage()
            ], 500);
        }
    }
}
