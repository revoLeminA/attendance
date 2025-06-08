<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class MailVerifyController extends Controller
{
    // メール認証誘導画面
    public function notice()
    {
        // すでにメール確認している場合は、ホーム画面にリダイレクト
        if (Auth::user() && !is_null(Auth::user()->email_verified_at)) {
            return redirect()->intended(RouteServiceProvider::HOME)->with('success', 'メール認証が完了しました。');
        }

        return view('auth.verify-email');
    }

    // メール認証処理
    public function verify(EmailVerificationRequest $request)
    {
        // email_verified_atに日時を挿入
        $request->fulfill();

        return view('auth.verified-notice');
    }

    // メール確認の再送信
    public function send()
    {
        // すでにメール確認している場合は、ホーム画面にリダイレクト
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // メール送信
        Auth::user()->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('success', '確認メールを送信しました。');
    }
}
