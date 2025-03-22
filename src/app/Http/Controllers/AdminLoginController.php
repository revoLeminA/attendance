<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\AdminLoginRequest;

class AdminLoginController extends Controller
{
    // 管理者ログイン画面
    public function create()
    {
        return view('auth.admin-login');
    }

    // 管理者ログイン
    public function store(AdminLoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('admin.attendance.index'));
    }

    // 管理者ログアウト
     public function destroy(Request $request)
     {
        // ログアウト
        Auth::guard('admin')->logout();
        // ユーザのセッション無効
        $request->session()->invalidate();
        // CSRFトークン無効
        $request->session()->regenerateToken();

        return redirect()->route('auth.admin-login');
    }
}
