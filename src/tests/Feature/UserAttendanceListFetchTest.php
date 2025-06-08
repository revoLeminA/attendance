<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAttendanceListFetchTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->now = Carbon::now();

        $months = [
            $this->now->copy()->subMonth(),  // 前月
            $this->now,                      // 今月
            $this->now->copy()->addMonth(),  // 翌月
        ];
        foreach ($months as $month) {
            // 3日分の勤怠データを作成（連続日）
            for ($i = 0; $i < 3; $i++) {
                $date = $month->copy()->addDays($i);
                $attendance = Attendance::factory()->create([
                    'user_id' => $this->user->id,
                    'status' => '退勤済み',
                    'date' => $date->format('Y-m-d'),
                    'clock_in' => $date->copy()->setTime(9, 0)->format('H:i'),
                    'clock_out' => $date->copy()->setTime(18, 0)->format('H:i'),
                ]);
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0)->format('H:i'),
                    'break_end' => $date->copy()->setTime(13, 0)->format('H:i'),
                ]);
            }
        }
    }

    /**
     * 自分の勤怠情報が全て表示されている
     */
    public function test_user_can_see_all_their_attendance_records(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('user.attendance.index'));

        // 3. 自分の勤怠情報がすべて表示されていることを確認する
        $thisMonth = $this->now;
        $attendances = Attendance::where('user_id', $this->user->id)->whereYear('date', $thisMonth->copy()->year)->whereMonth('date', $thisMonth->copy()->month)->get();

        // 自分の勤怠情報が全て表示されている
        foreach ($attendances as $attendance) {
            $this->assertMatchesRegularExpression(
                '/<tr[^>]*>.*?<td>\s*' . preg_quote($attendance->date->copy()->isoFormat('MM/DD（ddd）'), '/') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_in->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_out->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*1:00\s*<\/td>.*?<td>\s*8:00\s*<\/td>.*?<\/tr>/s',
                $response->getContent()
            );
        }
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_attendance_index_displays_current_month(): void
    {
        // 1. ユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('user.attendance.index'));

        // 現在の月が表示されている
        $response->assertSeeText($this->now->copy()->format('Y/m'));
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_attendance_displayed_on_click(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $this->get(route('user.attendance.index'));

        // 3. 「前月」ボタンを押す
        $previousMonth = $this->now->copy()->subMonth();
        $response = $this->get(route('user.attendance.index', ['year' => $previousMonth->copy()->format('Y'), 'month' => $previousMonth->copy()->format('m')]));

        // 前月の情報が表示されている
        $response->assertSeeText($previousMonth->copy()->format('Y/m'));
        $attendances = Attendance::where('user_id', $this->user->id)->whereYear('date', $previousMonth->copy()->year)->whereMonth('date', $previousMonth->copy()->month)->get();
        foreach ($attendances as $attendance) {
            $this->assertMatchesRegularExpression(
                '/<tr[^>]*>.*?<td>\s*' . preg_quote($attendance->date->copy()->isoFormat('MM/DD（ddd）'), '/') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_in->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_out->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*1:00\s*<\/td>.*?<td>\s*8:00\s*<\/td>.*?<\/tr>/s',
                $response->getContent()
            );
        }
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_attendance_displayed_on_click(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $this->get(route('user.attendance.index'));

        // 3. 「翌月」ボタンを押す
        $nextMonth = $this->now->copy()->addMonth();
        $response = $this->get(route('user.attendance.index', ['year' => $nextMonth->copy()->format('Y'), 'month' => $nextMonth->copy()->format('m')]));

        // 翌月の情報が表示されている
        $response->assertSeeText($nextMonth->copy()->format('Y/m'));
        $attendances = Attendance::where('user_id', $this->user->id)->whereYear('date', $nextMonth->copy()->year)->whereMonth('date', $nextMonth->copy()->month)->get();
        foreach ($attendances as $attendance) {
            $this->assertMatchesRegularExpression(
                '/<tr[^>]*>.*?<td>\s*' . preg_quote($attendance->date->copy()->isoFormat('MM/DD（ddd）'), '/') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_in->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*' . $attendance->clock_out->copy()->format('H:i') . '\s*<\/td>.*?<td>\s*1:00\s*<\/td>.*?<td>\s*8:00\s*<\/td>.*?<\/tr>/s',
                $response->getContent()
            );
        }
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_attendance_detail_navigation(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠一覧ページを開く
        $this->get(route('user.attendance.index'));

        // 3. 「詳細」ボタンを押下する
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $response = $this->get(route('auth.attendance.show', ['id' => Attendance::where('user_id', $this->user->id)->first()->id]));

        // その日の勤怠詳細画面に遷移する
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*名前\s*<\/th>.*?<td>.*?<span>\s*' . $this->user->name . '\s*<\/span>.*?\/td>.*?<\/tr>/s',
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
