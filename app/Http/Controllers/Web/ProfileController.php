<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function updateInfo(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ], [
            'name.required'  => '名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email'    => '有効なメールアドレスを入力してください。',
            'email.unique'   => 'このメールアドレスは既に使用されています。',
        ]);

        $user->update($data);

        return back()->with('success_info', 'プロフィールを更新しました。');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => '現在のパスワードを入力してください。',
            'password.required'         => '新しいパスワードを入力してください。',
            'password.confirmed'        => 'パスワードが一致しません。',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。'])
                ->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success_password', 'パスワードを変更しました。');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'パスワードを入力してください。',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['delete_password' => 'パスワードが正しくありません。'])
                ->withInput();
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'アカウントを削除しました。');
    }
}
