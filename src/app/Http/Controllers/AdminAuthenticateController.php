<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticateController extends Controller
{
    // 管理者ログイン画面
    public function create()
    {
        return view('auth.admin-login');
    }

    // 管理者ログイン
    public function store(AdminLoginRequest $request)
    {
        // 管理者ログイン認証
        $request->authenticate();
        // セッションIDの再発行
        $request->session()->regenerate();

        return redirect()->intended(route('admin.attendance.index'))->with('success', 'ログインしました。');
    }

    // 管理者ログアウト
    public function destroy(Request $request)
    {
        // ログアウト
        Auth::guard('admin')->logout();
        // ユーザのセッション無効
        $request->session()->invalidate();
        // CSRFトークンを再生成して、二重送信対策
        $request->session()->regenerateToken();

        return redirect()->route('auth.admin-login')->with('success', 'ログアウトしました。');
    }
}
