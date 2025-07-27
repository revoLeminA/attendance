<?php

namespace App\Http\Requests;

use Illuminate\Support\Arr;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.required' => ':attributeを入力してください',
            'email.email' => ':attributeは有効なメールアドレス形式で入力してください',
            'password.required' => ':attributeを入力してください',
            'password.min' => ':attributeは:min文字以上で入力してください'
        ]);
    }

    public function attributes(): array
    {
        return [
            'email' => 'メール',
            'password' => 'パスワード',
        ];
    }
}
