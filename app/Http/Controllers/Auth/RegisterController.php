<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'       => '名前を入力してください。',
            'email.required'      => 'メールアドレスを入力してください。',
            'email.email'         => '有効なメールアドレスを入力してください。',
            'email.unique'        => 'このメールアドレスは既に登録されています。',
            'password.required'   => 'パスワードを入力してください。',
            'password.min'        => 'パスワードは8文字以上で入力してください。',
            'password.confirmed'  => 'パスワードが一致しません。',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
