<?php

namespace App\Http\Controllers;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\RegPosyandu;
use App\Models\BarangMasuk;


use Illuminate\Routing\Controller;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $barang_kadaluarsa = BarangMasuk::where('tanggal_kadaluarsa', '<=', now()->addDays(30))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->count();
        
        return view('auth.login', compact('barang_kadaluarsa'));
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            // 'g-recaptcha-response' => 'required|captcha',
        ]);
    
        $credentials = $request->only('username', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role == 'admin') {
                Alert::success('Login Successful', 'Welcome back, Admin!');
                return redirect()->route('dashboard');
            } elseif ($user->role == 'pemilik_toko') {
                Alert::success('Login Successful', 'Welcome back, Pemilik Toko!');
                return redirect()->route('dashboard');
            } else {
                Auth::logout();
                Alert::error('Login Failed', 'You are not authorized to access this area.');
                return redirect('/');
            }
        }
    
        Alert::error('Login Failed', 'The provided credentials do not match our records.');
        return back();
    }
    


    /**
     * Handle logout.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        session()->flush();
        Alert::info('Logged Out', 'You have been logged out.');
        return redirect('/');
    }
}