<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminUserInfoFetchTest extends TestCase
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

        // 一般ユーザー3人作成
        $this->users = User::factory()->count(3)->create();

        // 勤怠情報作成
        foreach ($this->users as $index => $user) {
            $months = [
                $this->now->copy()->subMonth(),  // 前月
                $this->now,                      // 今月
                $this->now->copy()->addMonth(),  // 翌月
            ];

            foreach ($months as $baseDate) {
                $date = $baseDate->copy()->addDays($index); // ユーザーごとに日付をずらす

                // 時刻もユーザーごとにバラす
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'status' => '退勤済',
                    'date' => $date->copy()->format('Y/m/d'),
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
    }

    /**
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_see_all_general_users_name_and_email()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.staff.index'));

        // 全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
        foreach ($this->users as $user) {
            $response->assertSeeTextInOrder([
                $user->name,
                $user->email
            ]);
        }
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_view_selected_user_attendance_list()
    {
        // 1. 管理者ユーザーでログインする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 選択したユーザーの勤怠一覧ページを開く
        $user = $this->users->first();
        $response = $this->get(route('admin.staff.attendance.index', ['id' => $user->id]));

        // 勤怠情報が正確に表示される
        $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $this->now)->first();
        $response->assertSeeTextInOrder([
            $thisAttendance->date->copy()->format('Y/m'),
            $thisAttendance->date->copy()->isoFormat('MM/DD（ddd）'),
            $thisAttendance->clock_in->copy()->format('H:i'),
            $thisAttendance->clock_out->copy()->format('H:i'),
            '1:00', // 休憩時間
            '8:00'  // 合計時間
        ]);
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_view_previous_month_attendance()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        // 3. 「前月」ボタンを押す
        $user = $this->users->first();
        $previousMonth = $this->now->copy()->subMonth();
        $response = $this->get(route('admin.staff.attendance.index', ['id' => $user->id, 'year' => $previousMonth->copy()->format('Y'), 'month' => $previousMonth->copy()->format('m')]));

        // 前月の情報が表示されている
        $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $previousMonth)->first();
        $response->assertSeeTextInOrder([
            $thisAttendance->date->copy()->format('Y/m'),
            $thisAttendance->date->copy()->isoFormat('MM/DD（ddd）'),
            $thisAttendance->clock_in->copy()->format('H:i'),
            $thisAttendance->clock_out->copy()->format('H:i'),
            '1:00', // 休憩時間
            '8:00'  // 合計時間
        ]);
    }

    /**
     * 「翌月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_view_next_month_attendance()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        // 3. 「翌月」ボタンを押す
        $user = $this->users->first();
        $nextMonth = $this->now->copy()->addMonth();
        $response = $this->get(route('admin.staff.attendance.index', ['id' => $user->id, 'year' => $nextMonth->copy()->format('Y'), 'month' => $nextMonth->copy()->format('m')]));

        // 翌月の情報が表示されている
        $thisAttendance = Attendance::where('user_id', $user->id)->whereDate('date', $nextMonth)->first();
        $response->assertSeeTextInOrder([
            $thisAttendance->date->copy()->format('Y/m'),
            $thisAttendance->date->copy()->isoFormat('MM/DD（ddd）'),
            $thisAttendance->clock_in->copy()->format('H:i'),
            $thisAttendance->clock_out->copy()->format('H:i'),
            '1:00', // 休憩時間
            '8:00'  // 合計時間
        ]);
    }

    public function test_admin_can_navigate_to_attendance_detail()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $user = $this->users->first();
        $this->get(route('admin.staff.attendance.index', ['id' => $user->id]));

        // 3. 「詳細」ボタンを押下する
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $this->now)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $response = $this->get(route('auth.attendance.show', ['id' => $attendance->id]));

        // その日の勤怠詳細画面に遷移する（名前、日付、出勤・退勤時間、休憩時間が正しく表示されている）
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*名前\s*<\/th>.*?<td>.*?<span>\s*' . $user->name . '\s*<\/span>.*?\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*日付\s*<\/th>.*?<td>.*?<span>\s*' . $attendance->date->copy()->format('Y年') . '\s*<\/span>.*?<span>\s*' . $attendance->date->copy()->format('n月j日') . '\s*<\/span>.*?<input[^>]*?name="corrected_date"[^>]*?value="' . $attendance->date . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*出勤・退勤\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_clock_in"[^>]*?value="' . $attendance->clock_in->format('H:i') . '">.*?<label[^>]*>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_clock_out"[^>]*?value="' . $attendance->clock_out->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*休憩1\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_break_starts\[\]"[^>]*?value="' . $breakTime->break_start->format('H:i') . '">.*?<label[^>]*?>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_break_ends\[\]"[^>]*?value="' . $breakTime->break_end->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }
}
