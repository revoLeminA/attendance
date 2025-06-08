<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class StatusCheckTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    /**
     * ステータスに応じたユーザーをセットアップしてログイン
     */
    private function setupAndLoginUserByStatus(string $status): void
    {
        $this->user = User::factory()->create();

        if ($status !== '勤務外') {
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'status' => $status,
                'date' => Carbon::now()->format('Y/m/d'),
            ]);
        }

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);
    }

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_off_status_displays_correct_label(): void
    {
        // 1. ステータスが勤務外のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務外');

        // 2. 勤怠打刻画面を開く
        // 3. 画面に表示されているステータスを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に表示されているステータスが「勤務外」となる
        $response->assertSeeText('勤務外');
    }

    /**
     * 勤務中の場合、勤怠ステータスが正しく表示される
     */
    public function test_working_status_displays_correct_label(): void
    {
        // 1. ステータスが勤務中のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 勤怠打刻画面を開く
        // 3. 画面に表示されているステータスを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に表示されているステータスが「勤務中」となる
        $response->assertSeeText('勤務中');
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_break_status_displays_correct_label(): void
    {
        // 1. ステータスが休憩中のユーザーにログインする
        $this->setupAndLoginUserByStatus('休憩中');

        // 2. 勤怠打刻画面を開く
        // 3. 画面に表示されているステータスを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に表示されているステータスが「休憩中」となる
        $response->assertSeeText('休憩中');
    }

    /**
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_done_status_displays_correct_label(): void
    {
        // 1. ステータスが退勤済のユーザーにログインする
        $this->setupAndLoginUserByStatus('退勤済');

        // 2. 勤怠打刻画面を開く
        // 3. 画面に表示されているステータスを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に表示されているステータスが「退勤済」となる
        $response->assertSeeText('退勤済');
    }
}
