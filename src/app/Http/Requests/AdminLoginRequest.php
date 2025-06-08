<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }

    // 管理者ログイン認証
    public function authenticate()
    {
        // ログイン時に入力されたメールアドレスを持つユーザを取得
        $adminUser = User::where('email', $this->email)->first();

        // ユーザが管理者ロールで登録されていれば、パスワードの一致を確認してログイン
        if (isset($adminUser) and ($adminUser->role === 'admin') and Hash::check($this->password, $adminUser->password)) {
            return Auth::guard('admin')->login($adminUser);
        }

        // ログイン失敗
        throw ValidationException::withMessages(['email' => __('auth.failed')]);
    }
}
