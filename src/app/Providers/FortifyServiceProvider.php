<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\Events\Authenticated;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Auth;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        
        // 一般ユーザー用のログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 一般ユーザー用の会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // カスタム認証処理（一般ユーザ＆管理者共通）
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');

            // まず管理者としてログイン
            if (Auth::guard('admin')->attempt($credentials)) {
                return Auth::guard('admin')->user();
            }
            // 次に一般ユーザーとしてログイン
            if (Auth::guard('web')->attempt($credentials)) {
                return Auth::guard('web')->user();
            }

            return null;
        });
    }
}
