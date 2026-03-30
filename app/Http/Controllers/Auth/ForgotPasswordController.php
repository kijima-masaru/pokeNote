<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email'    => '有効なメールアドレスを入力してください。',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'パスワードリセット用のリンクをメールで送信しました。');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
