<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectedAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'corrected_date',
        'corrected_clock_in',
        'corrected_clock_out',
        'corrected_reason',
    ];

    protected $casts = [
        'corrected_date' => 'datetime',
        'corrected_clock_in' => 'datetime',
        'corrected_clock_out' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function corrected_break_times()
    {
        return $this->hasMany(CorrectedBreakTime::class);
    }

    public function attendances()
    {
        return $this->belongsTo(Attendance::class);
    }
}
