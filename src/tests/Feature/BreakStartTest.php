<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class BreakStartTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $now;

    /**
     * ステータスに応じたユーザーをセットアップしてログイン
     */
    private function setupAndLoginUserByStatus(string $status): void
    {
        $this->user = User::factory()->create();
        $this->now = Carbon::now();

        if ($status !== '勤務外') {
            $now = Carbon::now();
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'status' => $status,
                'date' => $this->now->copy()->format('Y/m/d'),
                'clock_in' => $this->now->copy()->copy()->format('H:i'),
            ]);
        }

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);
    }

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_break_start_button_works_correctly(): void
    {
        // 1. ステータスが出勤中のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 画面に「休憩入」ボタンが表示されていることを確認する
        $response = $this->get(route('user.attendance.create'));

        // 3. 休憩の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHour()->format('Y-m-d H:i:s'),
        ]);

        // 画面上に「休憩入」ボタンが表示され、処理後に画面上に表示されるステータスが「休憩中」になる
        $this->assertMatchesRegularExpression(
            '/<button[^>]*>\\s*休憩入\\s*<\/button>/',
            $response->getContent()
        );
        $this->get(route('user.attendance.create'))->assertSeeText('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_break_can_be_started_multiple_times_in_a_day(): void
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 休憩入と休憩戻の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHour()->format('Y-m-d H:i:s'),
        ]);
        $this->patch(route('user.attendance.update'), [
            'status' => '休憩中',
            'pushed_at' => $this->now->copy()->addHour()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        // 3. 「休憩入」ボタンが表示されることを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に「休憩入」ボタンが表示される
        $this->assertMatchesRegularExpression(
            '/<button[^>]*>\\s*休憩入\\s*<\/button>/',
            $response->getContent()
        );
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_break_end_button_works_correctly(): void
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 休憩入の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHour()->format('Y-m-d H:i:s'),
        ]);
        $response = $this->get(route('user.attendance.create'));

        // 3. 休憩戻の処理を行う
        $this->patch(route('user.attendance.update'), [
            'status' => '休憩中',
            'pushed_at' => $this->now->copy()->addHour()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        // 休憩戻ボタンが表示され、処理後にステータスが「出勤中」に変更される
        $this->assertMatchesRegularExpression(
            '/<button[^>]*>\\s*休憩戻\\s*<\/button>/',
            $response->getContent()
        );
        $this->get(route('user.attendance.create'))->assertSeeText('勤務中');
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_break_end_can_be_done_multiple_times(): void
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 休憩入と休憩戻の処理を行い、再度休憩入の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHour()->format('Y-m-d H:i:s'),
        ]);
        $this->patch(route('user.attendance.update'), [
            'status' => '休憩中',
            'pushed_at' => $this->now->copy()->addHour()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHour()->addMinutes(30)->addHour()->format('Y-m-d H:i:s'),
        ]);

        // 3. 「休憩戻」ボタンが表示されることを確認する
        $response = $this->get(route('user.attendance.create'));

        // 画面上に「休憩戻」ボタンが表示される
        $this->assertMatchesRegularExpression(
            '/<button[^>]*>\\s*休憩戻\\s*<\/button>/',
            $response->getContent()
        );
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_times_are_displayed_on_admin_panel(): void
    {
        // 1. ステータスが勤務中のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');
        $breakStartTime = $this->now->copy()->addHour();
        $breakEndTime = $breakStartTime->copy()->addMinutes(30);

        // 2. 休憩入と休憩戻の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $breakStartTime->copy()->format('Y-m-d H:i:s'),
        ]);
        $this->patch(route('user.attendance.update'), [
            'status' => '休憩中',
            'pushed_at' => $breakEndTime->copy()->format('Y-m-d H:i:s'),
        ]);

        // 3. 勤怠一覧画面から休憩の日付を確認する
        $response = $this->get(route('user.attendance.index'));

        // 勤怠一覧画面に休憩時刻が正確に記録されている
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<td>\s*' . preg_quote($breakStartTime->copy()->isoFormat('MM/DD（ddd）'), '/') . '\s*<\/td>.*?<td>\s*0:30\s*<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }
}
