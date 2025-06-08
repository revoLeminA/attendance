<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class DateTimeFetchTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->now = Carbon::now();
    }

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_is_formatted_like_ui(): void
    {
        // 1. 勤怠打刻画面を開く
        $response = $this->actingAs($this->user, 'web')->get(route('user.attendance.create'));

        // 2. 画面に表示されている日時情報を確認する
        // 画面上に表示されている日時が現在の日時と一致する
        $response->assertSeeText($this->now->copy()->format('Y年m月d日'));
        $response->assertSeeText($this->now->copy()->format('H:i'));
    }
}
