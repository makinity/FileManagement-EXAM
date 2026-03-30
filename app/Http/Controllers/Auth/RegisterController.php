<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function index(){
        return view('auth.register');
    }

    public function register(Request $request){
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5|confirmed',

            'terms' => 'required|accepted',
        ]);

        try{
            $user = User::create([
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

            Auth::login($user);

            return redirect()->route('dashboard.index');
        } catch(\Throwable $e) {
            report($e);

            $message = config('app.debug')
                ? 'Registration failed: ' . $e->getMessage()
                : 'Registration failed. Please try again.';

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['register' => $message]);
        }

    }
}
