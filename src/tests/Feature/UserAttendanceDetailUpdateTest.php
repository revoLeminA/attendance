<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectedAttendance;

class UserAttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $now;

    /**
     * 勤怠情報が登録されたユーザーを作成
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->now = Carbon::now();

        // 3日分の勤怠データを作成（連続日）
        for ($i = 0; $i < 3; $i++) {
            $date = $this->now->copy()->addDays($i);
            $attendance = Attendance::factory()->create([
                'user_id' => $this->user->id,
                'status' => '退勤済',
                'date' => $date->copy()->format('Y/m/d'),
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

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_clock_in_after_clock_out_shows_validation_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->get(route('auth.attendance.show', ['id' => $attendance->id]));

        // 3. 出勤時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_clock_in' => $this->now->copy()->setTime(19, 0)->format('H:i'),
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_start_after_clock_out_shows_validation_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->get(route('auth.attendance.show', ['id' => $attendance->id]));

        // 3. 休憩開始時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_break_starts' => [$this->now->copy()->setTime(19, 0)->format('H:i')],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_break_starts.0' => '休憩時間が勤務時間外です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_end_after_clock_out_shows_validation_error(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->get(route('auth.attendance.show', ['id' => $attendance->id]));

        // 3. 休憩終了時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_break_ends' => [$this->now->copy()->setTime(19, 0)->format('H:i')],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 「休憩時間が勤務時間外です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['corrected_break_ends.0' => '休憩時間が勤務時間外です']);
    }

    /**
     * 備考欄が未入力の場合,「備考を記入してください」というバリデーションメッセージが表示される
     */
    public function test_corrected_reason_required_validation(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細ページを開く
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->get(route('auth.attendance.show', ['id' => $attendance->id]));

        // 3. 備考欄を未入力のまま保存処理をする
        $response = $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_reason' => '',
        ]);

        $response->assertSessionHasErrors(['corrected_reason' => '備考を記入してください']);
    }

    /**
     * 修正申請処理が実行される
     */
    public function test_corrected_attendance_request_is_created_and_displayed(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_date' => $attendance->date->copy()->format('Y/m/d'),
            'corrected_clock_in' => $this->now->copy()->setTime(8, 0)->format('H:i'),
            'corrected_clock_out' => $this->now->copy()->setTime(17, 0)->format('H:i'),
            'corrected_break_starts' => [$this->now->copy()->setTime(11, 30)->format('H:i')],
            'corrected_break_ends' => [$this->now->copy()->setTime(12, 30)->format('H:i')],
            'break_time_ids' => [$breakTime->id],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 3. 管理者ユーザーで承認画面と申請一覧画面を確認する
        $admin = User::factory()->create(['role' => 'admin'])->first();
        $responseIndex = $this->actingAs($admin, 'admin')->get(route('auth.request.index', ['tab' => 'wait']));

        // 修正申請が実行され、管理者の承認画面と申請一覧画面に表示される
        $responseIndex->assertSeeTextInOrder([
            '承認待ち',
            $this->user->name,
            $attendance->date->format('Y/m/d'),
            '修正申請テスト',
            $this->now->copy()->format('Y/m/d'),
        ]);
        $responseShow = $this->actingAs($admin, 'admin')->get(route('admin.request.show', ['attendance_correct_request' => CorrectedAttendance::where('user_id', $this->user->id)->first()->id]));
        $responseShow->assertSeeTextInOrder([
            '名前',
            $this->user->name,
            '日付',
            $this->now->copy()->format('Y年'),
            $this->now->copy()->format('n月j日'),
            '出勤・退勤',
            $this->now->copy()->setTime(8, 0)->format('H:i'),
            '～',
            $this->now->copy()->setTime(17, 0)->format('H:i'),
            '休憩1',
            $this->now->copy()->setTime(11, 30)->format('H:i'),
            '～',
            $this->now->copy()->setTime(12, 30)->format('H:i'),
            '備考',
            '修正申請テスト',
        ]);
    }

    /**
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_corrected_attendance_requests_are_displayed_for_user(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $attendances = Attendance::where('user_id', $this->user->id)->get();
        $breakTimes = BreakTime::whereIn('attendance_id', $attendances->pluck('id'))->get();
        foreach ($attendances as $index => $attendance) {
            $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
                'corrected_date' => $attendance->date->copy()->format('Y/m/d'),
                'corrected_clock_in' => $this->now->copy()->setTime(8 - $index, 0)->format('H:i'),
                'corrected_clock_out' => $this->now->copy()->setTime(17 - $index, 0)->format('H:i'),
                'corrected_break_starts' => [$this->now->copy()->setTime(11, 30)->format('H:i')],
                'corrected_break_ends' => [$this->now->copy()->setTime(12, 30)->format('H:i')],
                'break_time_ids' => [$breakTimes[$index]->id],
                'corrected_reason' => "修正申請テスト{$index}",
            ]);
        }

        // 3. 申請一覧画面を確認する
        $response = $this->get(route('auth.request.index', ['tab' => 'wait']));

        // 申請一覧に自分の申請が全て表示されている
        foreach ($attendances as $index => $attendance) {
            $response->assertSeeTextInOrder([
                '承認待ち',
                $this->user->name,
                $attendance->date->copy()->format('Y/m/d'),
                "修正申請テスト{$index}",
                $this->now->copy()->format('Y/m/d'),
            ]);
        }
    }

    /**
     * 「承認済み」に管理者が承認した申請が全て表示されている
     */
    public function test_corrected_attendance_approved_requests_are_displayed_for_user(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $attendances = Attendance::where('user_id', $this->user->id)->get();
        $breakTimes = BreakTime::whereIn('attendance_id', $attendances->pluck('id'))->get();
        foreach ($attendances as $index => $attendance) {
            $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
                'corrected_date' => $attendance->date->copy()->format('Y/m/d'),
                'corrected_clock_in' => $this->now->copy()->setTime(8 - $index, 0)->format('H:i'),
                'corrected_clock_out' => $this->now->copy()->setTime(17 - $index, 0)->format('H:i'),
                'corrected_break_starts' => [$this->now->copy()->setTime(11, 30)->format('H:i')],
                'corrected_break_ends' => [ $this->now->copy()->setTime(12, 30)->format('H:i')],
                'break_time_ids' => [$breakTimes[$index]->id],
                'corrected_reason' => "修正申請テスト{$index}",
            ]);
        }
        $admin = User::factory()->create(['role' => 'admin'])->first();
        foreach ($attendances as $index => $attendance) {
            $correctedAttendanceId = CorrectedAttendance::where('attendance_id', $attendance->id)->first()->id;
            $this->actingAs($admin, 'admin')->patch(route('admin.request.update', ['attendance_correct_request' => $correctedAttendanceId]), ['attendance_correct_request' => $correctedAttendanceId]);
        }

        // 3. 申請一覧画面を開く
        $response = $this->get(route('auth.request.index', ['tab' => 'approve']));

        // 4. 管理者が承認した修正申請が全て表示されていることを確認
        // 承認済みに管理者が承認した申請が全て表示されている

        foreach ($attendances as $index => $attendance) {
            $response->assertSeeTextInOrder([
                '承認済み',
                $this->user->name,
                $attendance->date->copy()->format('Y/m/d'),
                "修正申請テスト{$index}",
                $this->now->copy()->format('Y/m/d'),
            ]);
        }
    }

    /**
     * 各申請の「詳細」を押下すると申請詳細画面に遷移する
     */
    public function test_corrected_attendance_request_detail_navigation(): void
    {
        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // 2. 勤怠詳細を修正し保存処理をする
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $breakTime = BreakTime::where('attendance_id', $attendance->id)->first();
        $this->patch(route('auth.attendance.update', ['id' => $attendance->id]), [
            'corrected_date' => $attendance->date->copy()->format('Y/m/d'),
            'corrected_clock_in' => $this->now->copy()->setTime(8, 0)->format('H:i'),
            'corrected_clock_out' => $this->now->copy()->setTime(17, 0)->format('H:i'),
            'corrected_break_starts' => [$this->now->copy()->setTime(11, 30)->format('H:i')],
            'corrected_break_ends' => [ $this->now->copy()->setTime(12, 30)->format('H:i')],
            'break_time_ids' => [$breakTime->id],
            'corrected_reason' => '修正申請テスト',
        ]);

        // 3. 申請一覧画面を開く
        $this->get(route('auth.request.index', ['tab' => 'wait']));


        // 4. 「詳細」ボタンを押す
        $response = $this->get(route('auth.attendance.show', ['id' => CorrectedAttendance::latest('id')->first()->attendance_id]));

        // 申請詳細画面に遷移する
        $response->assertSeeTextInOrder([
            '名前',
            $this->user->name,
            '日付',
            $this->now->copy()->format('Y年'),
            $this->now->copy()->format('n月j日'),
            '出勤・退勤',
            $this->now->copy()->setTime(8, 0)->format('H:i'),
            '～',
            $this->now->copy()->setTime(17, 0)->format('H:i'),
            '休憩1',
            $this->now->copy()->setTime(11, 30)->format('H:i'),
            '～',
            $this->now->copy()->setTime(12, 30)->format('H:i'),
            '備考',
            '修正申請テスト',
            '*承認待ちのため修正はできません',
        ]);
    }
}
