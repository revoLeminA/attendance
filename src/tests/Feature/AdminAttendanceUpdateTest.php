<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectedAttendance;
use App\Models\CorrectedBreakTime;
use Carbon\Carbon;

class AdminAttendanceUpdateTest extends TestCase
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
        $this->admin = User::factory()->create(['role' => 'admin',]);

        // 一般ユーザー(3人)作成
        $this->users = User::factory()->count(3)->create();

        // 勤怠情報作成
        foreach ($this->users as $index => $user) {
            $date = $this->now->copy()->addDays($index);
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'status' => '退勤済',
                'date' => $date->format('Y-m-d'),
                'clock_in' => $date->copy()->setTime(9 + $index, 0)->format('H:i'),
                'clock_out' => $date->copy()->setTime(18 + $index, 0)->format('H:i'),
            ]);
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12 + $index, 0)->format('H:i'),
                'break_end' => $date->copy()->setTime(13 + $index, 0)->format('H:i'),
            ]);
        }
    }

    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function test_pending_corrections_are_displayed()
    {
        foreach ($this->users as $index => $user) {
            $attendance = Attendance::where('user_id', $user->id)->first();
            CorrectedAttendance::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'corrected_date' => $attendance->date,
                'corrected_clock_in' => $attendance->clock_in->copy()->addMinutes(30)->format('H:i'),
                'corrected_clock_out' => $attendance->clock_out->copy()->addMinutes(30)->format('H:i'),
                'corrected_reason' => "修正申請テスト{$index}",
                'status' => '承認待ち',
            ]);
        }

        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く
        $response = $this->get(route('auth.request.index', ['tab' => 'wait']));

        // 全ユーザーの未承認の修正申請が表示される
        foreach ($this->users as $index => $user) {
            $attendance = Attendance::where('user_id', $user->id)->first();
            $response->assertSeeTextInOrder([
                '承認待ち',
                $user->name,
                $attendance->date->format('Y/m/d'),
                "修正申請テスト{$index}",
                $this->now->copy()->format('Y/m/d'),
            ]);
        }
    }

    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function test_approved_corrections_are_displayed()
    {
        foreach ($this->users as $index => $user) {
            $attendance = Attendance::where('user_id', $user->id)->first();
            CorrectedAttendance::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'corrected_date' => $attendance->date,
                'corrected_clock_in' => $attendance->clock_in->copy()->addMinutes(30)->format('H:i'),
                'corrected_clock_out' => $attendance->clock_out->copy()->addMinutes(30)->format('H:i'),
                'corrected_reason' => "修正申請テスト{$index}",
                'status' => '承認済み',
            ]);
        }

        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 修正申請一覧ページを開き、承認済みのタブを開く
        $response = $this->get(route('auth.request.index', ['tab' => 'approve']));

        // 全ユーザーの承認済みの修正申請が表示される
        foreach ($this->users as $index => $user) {
            $attendance = Attendance::where('user_id', $user->id)->first();
            $response->assertSeeTextInOrder([
                '承認済み',
                $user->name,
                $attendance->date->format('Y/m/d'),
                "修正申請テスト{$index}",
                $this->now->copy()->format('Y/m/d'),
            ]);
        }
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function test_correction_detail_is_displayed_correctly()
    {
        $user = $this->users[0];
        $attendance = Attendance::where('user_id', $user->id)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $correctedAttendance = CorrectedAttendance::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_date' => $attendance->date->copy()->format('Y-m-d'),
            'corrected_clock_in' => $attendance->clock_in->copy()->addMinutes(30)->format('H:i'),
            'corrected_clock_out' => $attendance->clock_out->copy()->addMinutes(30)->format('H:i'),
            'corrected_reason' => '修正申請テスト',
            'status' => '承認待ち',
        ]);
        $correctedBreakTime = CorrectedBreakTime::factory()->create([
            'break_time_id' => $breakTime->id,
            'corrected_attendance_id' => $correctedAttendance->id,
            'corrected_break_start' => $breakTime->break_start->copy()->addMinutes(15)->format('H:i'),
            'corrected_break_end' => $breakTime->break_end->copy()->addMinutes(15)->format('H:i'),
        ]);

        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 修正申請の詳細画面を開く
        $response = $this->get(route('admin.request.show', ['attendance_correct_request' => $correctedAttendance->id]));

        // 申請内容が正しく表示されている
        $response->assertSeeTextInOrder([
            '名前',
            $user->name,
            '日付',
            $correctedAttendance->corrected_date->copy()->format('Y年'),
            $correctedAttendance->corrected_date->copy()->format('n月j日'),
            '出勤・退勤',
            $correctedAttendance->corrected_clock_in->copy()->format('H:i'),
            '～',
            $correctedAttendance->corrected_clock_out->copy()->format('H:i'),
            '休憩1',
            $correctedBreakTime->corrected_break_start->copy()->format('H:i'),
            '～',
            $correctedBreakTime->corrected_break_end->copy()->format('H:i'),
            '備考',
            '修正申請テスト',
        ]);
    }

    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function test_admin_can_approve_correction_and_attendance_is_updated()
    {
        $user = $this->users[0];
        $attendance = Attendance::where('user_id', $user->id)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $correctedAttendance = CorrectedAttendance::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_date' => $attendance->date->copy()->format('Y-m-d'),
            'corrected_clock_in' => $attendance->clock_in->copy()->addMinutes(30)->format('H:i'),
            'corrected_clock_out' => $attendance->clock_out->copy()->addMinutes(30)->format('H:i'),
            'corrected_reason' => '修正申請テスト',
            'status' => '承認待ち',
        ]);
        $correctedBreakTime = CorrectedBreakTime::factory()->create([
            'break_time_id' => $breakTime->id,
            'corrected_attendance_id' => $correctedAttendance->id,
            'corrected_break_start' => $breakTime->break_start->copy()->addMinutes(15)->format('H:i'),
            'corrected_break_end' => $breakTime->break_end->copy()->addMinutes(15)->format('H:i'),
        ]);

        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 修正申請の詳細画面で「承認」ボタンを押す
        $this->patch(route('admin.request.update', ['attendance_correct_request' => $correctedAttendance->id]), ['attendance_correct_request' => $correctedAttendance->id]);

        // 修正申請が承認され、勤怠情報が更新される
        $this->assertDatabaseHas('corrected_attendances', [
            'id' => $correctedAttendance->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'corrected_date' => $correctedAttendance->corrected_date->copy()->format('Y-m-d'),
            'corrected_clock_in' => $correctedAttendance->corrected_clock_in->copy()->format('H:i:s'),
            'corrected_clock_out' => $correctedAttendance->corrected_clock_out->copy()->format('H:i:s'),
            'corrected_reason' => '修正申請テスト',
            'status' => '承認済み',
        ]);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '退勤済',
            'date' => $correctedAttendance->corrected_date->copy()->format('Y-m-d'),
            'clock_in' => $correctedAttendance->corrected_clock_in->copy()->format('H:i:s'),
            'clock_out' => $correctedAttendance->corrected_clock_out->copy()->format('H:i:s'),
        ]);
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => $correctedBreakTime->corrected_break_start->copy()->format('H:i:s'),
            'break_end' => $correctedBreakTime->corrected_break_end->copy()->format('H:i:s'),
        ]);
    }
}
