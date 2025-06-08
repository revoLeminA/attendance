<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
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
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'status' => $status,
                'date' => $this->now ->copy()->format('Y/m/d'),
                'clock_in' => $this->now ->copy()->copy()->format('H:i'),
            ]);
        }

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);
    }

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_works_correctly(): void
    {
        // 1. ステータスが勤務中のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務中');

        // 2. 画面に「退勤」ボタンが表示されていることを確認する
        $response = $this->get(route('user.attendance.create'));
        $response->assertSee('退勤');

        // 3. 退勤の処理を行う
        $this->patch(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $this->now->copy()->addHours(4)->format('Y-m-d H:i:s'),
        ]);

        // 画面上に「退勤」ボタンが表示され、処理後に画面上に表示されるステータスが「退勤済」になる
        $this->assertMatchesRegularExpression(
            '/<button[^>]*>\\s*退勤\\s*<\/button>/',
            $response->getContent()
        );
        $this->get(route('user.attendance.create'))->assertSeeText('退勤済');
    }

    /**
     * 退勤時刻が管理画面で確認できる
     */
    public function test_clock_out_time_is_displayed_in_admin_panel(): void
    {
        // 1. ステータスが勤務外のユーザーにログインする
        $this->setupAndLoginUserByStatus('勤務外');
        $clockInTime = $this->now;
        $clockOutTime = $clockInTime->copy()->addHours(4);

        // 2. 出勤と退勤の処理を行う
        $this->post(route('user.attendance.store'), [
            'status' => '勤務外',
            'pushed_at' => $clockInTime->copy()->format('Y-m-d H:i:s'),
        ]);
        $this->patch(route('user.attendance.store'), [
            'status' => '勤務中',
            'pushed_at' => $clockOutTime->copy()->format('Y-m-d H:i:s'),
        ]);

        // 3. 管理画面から退勤の日付を確認する
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        // 管理画面に退勤時刻が正確に記録されている
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<td>\s*' . $this->user->name . '\s*<\/td>.*?<td>\s*' . $clockInTime->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*' . $clockOutTime->copy()->format('H:i') . '\s*<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }
}
