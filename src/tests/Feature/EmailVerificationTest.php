<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後、認証メールが送信される
     */
    public function test_email_verification_mail_is_sent_after_registering()
    {
        Notification::fake();
        // 事前に通知が送信されていないことを確認
        Notification::assertNothingSent();

        // 1. 会員登録をする
        // 2. 認証メールを送信する
        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $user = User::where('email', 'test@example.com')->first();

        // 登録したメールアドレス宛に認証メールが一度だけ送信されている
        Notification::assertSentTo($user, VerifyEmail::class);
        Notification::assertTimesSent(1, VerifyEmail::class);
    }

    /**
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_redirect_to_verification_url_when_click_verify_button()
    {
        Notification::fake();
        // 事前に通知が送信されていないことを確認
        Notification::assertNothingSent();

        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $user = User::where('email', 'test@example.com')->first();

        // 1. メール認証導線画面を表示する
        $this->get('/email/verify');

        // 2. 「認証はこちらから」ボタンを押下
        // 3. メール認証サイトを表示する
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $response = $this->get($verificationUrl);

        // メール認証サイトに遷移する
        $response->assertSeeTextInOrder([
            '登録していただいたメールアドレスの確認が完了しました。',
            '元の画面から認証を完了してください。',
        ]);
    }

    /**
     * メール認証サイトのメール認証を完了すると、商品一覧ページに遷移する
     */
    public function user_can_verify_email_and_redirect_to_product_page()
    {
        // 1. メール認証を完了する
        // 2. 商品一覧画面を表示する
        Event::fake();

        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $user = User::where('email', 'test@example.com')->first();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);

        $this->assertNotNull($user->fresh()->email_verified_at);

        // 勤怠登録画面に遷移する
        $response->assertRedirect('/attendance');
    }
}
