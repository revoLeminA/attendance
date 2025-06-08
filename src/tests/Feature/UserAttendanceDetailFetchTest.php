<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAttendanceDetailFetchTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $now;
    private $attendance;
    private $breakTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->now = Carbon::now();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'status' => '退勤済',
            'date' => $this->now->copy()->format('Y/m/d'),
            'clock_in' => $this->now->copy()->setTime(9, 0)->format('H:i'),
            'clock_out' => $this->now->copy()->setTime(18, 0)->format('H:i'),
        ]);

        $this->breakTime = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => $this->now->copy()->setTime(12, 0)->format('H:i'),
            'break_end' => $this->now->copy()->setTime(13, 0)->format('H:i'),
        ]);
    }

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_name_is_logged_in_user_name(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 名前欄を確認する
        // 名前がログインユーザーの名前になっている
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*名前\s*<\/th>.*?<td>.*?<span>\s*' . $this->user->name . '\s*<\/span>.*?\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_date_is_correct(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 日付欄を確認する
        // 日付が選択した日付になっている
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*日付\s*<\/th>.*?<td>.*?<span>\s*' . $this->attendance->date->copy()->format('Y年') . '\s*<\/span>.*?<span>\s*' . $this->attendance->date->copy()->format('n月j日') . '\s*<\/span>.*?<input[^>]*?name="corrected_date"[^>]*?value="' . $this->attendance->date . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_clock_in_and_out_time_are_correct(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 出勤・退勤欄を確認する
        // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*出勤・退勤\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_clock_in"[^>]*?value="' . $this->attendance->clock_in->format('H:i') . '">.*?<label[^>]*>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_clock_out"[^>]*?value="' . $this->attendance->clock_out->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_break_time_is_correct(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 休憩欄を確認する
        // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*休憩1\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_break_starts\[\]"[^>]*?value="' . $this->breakTime->break_start->format('H:i') . '">.*?<label[^>]*?>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_break_ends\[\]"[^>]*?value="' . $this->breakTime->break_end->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }
}
