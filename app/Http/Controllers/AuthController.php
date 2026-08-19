<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle user login request
     */
    public function login(Request $request)
    {
        $user = User::with('store')
                ->where('email', $request->email)
                ->first();
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 🔥 LOGIN PAKE AUTH (INI KUNCI)
        if (Auth::attempt($credentials)) {

            $request->session()->regenerate(); // penting

            $user = Auth::user();

            // Optional: simpan ke session (biar blade kamu tetap jalan)
            session(['user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_display_name' => $user->role_display_name,
                'site_code' => $user->site_code,
                'store_name' => $user->store->name ?? null,
            ]]);

            // 🔥 REDIRECT BERDASARKAN ROLE
            return match($user->role) {
                'store_manager' => redirect()->route('dashboard.store-manager'),
                'area_manager' => redirect()->route('dashboard.area-manager'),
                'kasir' => redirect()->route('dashboard.kasir'),
                'admin' => redirect()->route('dashboard.store-manager'),
                default => redirect()->route('dashboard.kasir'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ])->withInput($request->only('email'));
    }

    /**
     * Handle user logout request
     */
    public function logout(Request $request)
    {
        Auth::logout(); // 🔥 WAJIB

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Get authenticated user profile
     */
    public function profile()
    {
        return response()->json([
            'success' => true,
            'data' => Auth::user()
        ]);
    }

    /**
     * Check user role permissions
     */
    public function checkPermissions()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'role' => $user->role,
                'permissions' => [
                    'is_store_manager' => $user->role === 'store_manager',
                    'is_area_manager' => $user->role === 'area_manager',
                    'is_kasir' => $user->role === 'kasir',
                ]
            ]
        ]);
    }
}