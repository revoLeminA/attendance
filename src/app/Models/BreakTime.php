<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    // データを取得する際に型変換する
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function attendances()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function corrected_break_times()
    {
        return $this->hasMany(CorrectedBreakTime::class);
    }
}
