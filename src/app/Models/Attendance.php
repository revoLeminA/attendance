<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'date',
        'clock_in',
        'clock_out',
    ];

    // データを取得する際に型変換する
    protected $casts = [
        'date' => 'datetime',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function break_times()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function corrected_attendances()
    {
        return $this->hasOne(CorrectedAttendance::class);
    }
}
