<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function showForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'      => 'メールアドレスを入力してください。',
            'email.email'         => '有効なメールアドレスを入力してください。',
            'password.required'   => 'パスワードを入力してください。',
            'password.min'        => 'パスワードは8文字以上で入力してください。',
            'password.confirmed'  => 'パスワードが一致しません。',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'パスワードを再設定しました。新しいパスワードでログインしてください。');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
