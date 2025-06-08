<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRegisterAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required(): void
    {
        // 1. 名前以外のユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required(): void
    {
        // 1. メールアドレス以外のユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     *  パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        // 1. パスワードを8文字未満にし、ユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        // 「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     *  パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_must_match(): void
    {
        // 1. 確認用のパスワードとパスワードを一致させず、ユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        // 「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required(): void
    {
        // 1. パスワード以外のユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 入力内容が正しい場合、ユーザーが登録される
     */
    public function test_user_is_registered_with_valid_input(): void
    {
        // 1. ユーザー情報を入力する
        // 2. 会員登録の処理を行う
        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // データベースに登録したユーザー情報が保存される
        $this->assertDatabaseHas(User::class, [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
        // ハッシュ化されたパスワードが正しく保存されていることを確認
        $this->assertTrue(Hash::check('password123', User::where('email', 'test@example.com')->first()->password));
    }
}
