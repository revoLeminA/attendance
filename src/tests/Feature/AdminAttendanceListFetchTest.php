<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceListFetchTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $users;
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::now();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create(['role' => 'admin']);

        // 一般ユーザー複数作成とそれぞれの勤怠データ作成（前日、今日、翌日）
        $this->users = User::factory()->count(3)->create();

        foreach ($this->users as $user) {
            // 前日、今日、翌日の勤怠データを作成
            $days = [
                $this->now->copy()->subDay(), // 前日
                $this->now,                   // 今日
                $this->now->copy()->addDay()  // 翌日
            ];
            foreach ($days as $index => $day) {
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'status' => '退勤済',
                    'date' => $day->copy()->format('Y-m-d'),
                    'clock_in' => $day->copy()->setTime(9 + $index, 0)->format('H:i'),
                    'clock_out' => $day->copy()->setTime(18 + $index, 0)->format('H:i'),
                ]);
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $day->copy()->setTime(12 + $index, 0)->format('H:i'),
                    'break_end' => $day->copy()->setTime(13 + $index, 0)->format('H:i'),
                ]);
            }
        }
    }

    /**
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_attendance_list_displays_current_date_data()
    {
        // 1. 管理者ユーザーにログインする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.index'));

        // その日の全ユーザーの勤怠情報が正確な値になっている
        foreach ($this->users as $user) {
            $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $this->now->copy()->format('Y-m-d'))->first();
            $response->assertSeeTextInOrder([
                $user->name,
                $thisAttendance->clock_in->copy()->format('H:i'),
                $thisAttendance->clock_out->copy()->format('H:i'),
                '1:00', // 休憩
                '8:00'  // 合計
            ]);
        }
    }

    /**
     * 遷移した際に現在の日付が表示される勤怠一覧画面にその日の日付が表示されている
     */
    public function test_attendance_list_displays_current_date_on_initial_load()
    {
        // 1. 管理者ユーザーにログインする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.index'));

        // 勤怠一覧画面にその日の日付が表示されている
        $response->assertSeeText($this->now->copy()->format('Y/m/d'));
    }

    /**
     * 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_attendance_list_displays_previous_date_data()
    {
        // 1. 管理者ユーザーにログインする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.index'));

        // 3. 「前日」ボタンを押す
        $previousDate = $this->now->copy()->subDay();
        $response = $this->get(route('admin.attendance.index', ['year' => $previousDate->copy()->format('Y'), 'month' => $previousDate->copy()->format('m'), 'day' => $previousDate->copy()->format('d')]));

        // 前日の日付の勤怠情報が表示される
        $response->assertSee($previousDate->copy()->format('Y/m/d'));
        foreach ($this->users as $user) {
            $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $previousDate->copy()->format('Y-m-d'))->first();
            $response->assertSeeTextInOrder([
                $user->name,
                $thisAttendance->clock_in->copy()->format('H:i'),
                $thisAttendance->clock_out->copy()->format('H:i'),
                '1:00', // 休憩
                '8:00'  // 合計
            ]);
        }
    }

    /**
     * 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_attendance_list_displays_next_date_data()
    {
        // 1. 管理者ユーザーにログインする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.index'));

        // 3. 「翌日」ボタンを押す
        $nextDate = $this->now->copy()->addDay();
        $response = $this->get(route('admin.attendance.index', ['year' => $nextDate->copy()->format('Y'), 'month' => $nextDate->copy()->format('m'), 'day' => $nextDate->copy()->format('d')]));

        // 翌日の日付の勤怠情報が表示される
        $response->assertSeeText($nextDate->copy()->format('Y/m/d'));
        foreach ($this->users as $user) {
            $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $nextDate->copy()->format('Y-m-d'))->first();
            $response->assertSeeTextInOrder([
                $user->name,
                $thisAttendance->clock_in->copy()->format('H:i'),
                $thisAttendance->clock_out->copy()->format('H:i'),
                '1:00', // 休憩
                '8:00'  // 合計
            ]);
        }
    }
}
