<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceDetailEditTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $now;
    private $attendance;
    private $breakTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::now();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create(['role' => 'admin']);

        // 一般ユーザー作成
        $this->user = User::factory()->create();

        // 勤怠情報作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'status' => '退勤済',
            'date' => $this->now->format('Y/m/d'),
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
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_attendance_detail_displays_correct_data()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 詳細画面の内容が選択した情報と一致する
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*名前\s*<\/th>.*?<td>.*?<span>\s*' . $this->user->name . '\s*<\/span>.*?\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*日付\s*<\/th>.*?<td>.*?<span>\s*' . $this->attendance->date->copy()->format('Y年') . '\s*<\/span>.*?<span>\s*' . $this->attendance->date->copy()->format('n月j日') . '\s*<\/span>.*?<input[^>]*?name="corrected_date"[^>]*?value="' . $this->attendance->date . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*出勤・退勤\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_clock_in"[^>]*?value="' . $this->attendance->clock_in->copy()->format('H:i') . '">.*?<label[^>]*>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_clock_out"[^>]*?value="' . $this->attendance->clock_out->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/<tr[^>]*>.*?<th>\s*休憩1\s*<\/th>.*?<td>.*?<input[^>]*?name="corrected_break_starts\[\]"[^>]*?value="' . $this->breakTime->break_start->copy()->format('H:i') . '">.*?<label[^>]*?>\s*～\s*<\/label>.*?<input[^>]*?name="corrected_break_ends\[\]"[^>]*?value="' . $this->breakTime->break_end->copy()->format('H:i') . '">.*?<\/td>.*?<\/tr>/s',
            $response->getContent()
        );
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_clock_in_after_clock_out_shows_validation_error()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 出勤時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $this->attendance->id]), [
            'corrected_clock_in' => $this->now->copy()->setTime(19, 0)->format('H:i'),
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_start_after_clock_out_shows_validation_error()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 休憩開始時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $this->attendance->id]), [
            'corrected_break_starts' => [$this->now->copy()->setTime(19, 0)->format('H:i')],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_break_starts.0' => '休憩時間が勤務時間外です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_end_after_clock_out_shows_validation_error()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 休憩終了時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $this->attendance->id]), [
            'corrected_break_ends' => [$this->now->copy()->setTime(19, 0)->format('H:i')],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_break_ends.0' => '休憩時間が勤務時間外です']);
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_corrected_reason_required_validation()
    {
        // 1. 管理者ユーザーにログインをする
        $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $this->get(route('auth.attendance.show', ['id' => $this->attendance->id]));

        // 3. 備考欄を未入力のまま保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $this->attendance->id]), [
            'corrected_clock_in' => $this->attendance->clock_in->copy()->format('H:i'),
            'corrected_clock_out' => $this->attendance->clock_out->copy()->format('H:i'),
            'corrected_reason' => '',
        ]);

        // 「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_reason' => '備考を記入してください']);
    }
}
