<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        return view('auth.login');
    }

    public function forgot(){
        return view('auth.forgot-password');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->route('dashboard.index');
            }

            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');

        } catch (\Throwable $e) {
            report($e);

            $message = config('app.debug')
                ? 'Login failed: ' . $e->getMessage()
                : 'Login failed. Please try again.';

            return back()->withErrors([
                'email' => $message,
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request){
        Auth::logout();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
