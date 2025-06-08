<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectedBreakTime extends Model
{
    use HasFactory;

    protected $table = 'corrected_break_times';

    protected $fillable = [
        'break_time_id',
        'corrected_attendance_id',
        'corrected_break_start',
        'corrected_break_end',
    ];

    // データを取得する際に型変換する
    protected $casts = [
        'corrected_break_start' => 'datetime',
        'corrected_break_end' => 'datetime',
    ];

    public function break_times()
    {
        return $this->belongsTo(BreakTime::class);
    }

    public function corrected_attendances()
    {
        return $this->belongsTo(CorrectedAttendance::class);
    }
}
