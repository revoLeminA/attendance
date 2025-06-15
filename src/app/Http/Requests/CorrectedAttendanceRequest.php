<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectedAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'corrected_clock_in' => 'before:corrected_clock_out',
            'corrected_break_starts.*' => 'after:corrected_clock_in|before:corrected_clock_out',
            'corrected_break_ends.*' => 'after:corrected_clock_in|before:corrected_clock_out',
            'corrected_break_start_add' => 'nullable|after:corrected_clock_in|before:corrected_clock_out',
            'corrected_break_end_add' => 'nullable|after:corrected_clock_in|before:corrected_clock_out',
            'corrected_reason' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'corrected_clock_in.before' => ':attributeもしくは退勤時間が不適切な値です',
            'corrected_break_starts.*.after' => '休憩時間が勤務時間外です',
            'corrected_break_starts.*.before' => '休憩時間が勤務時間外です',
            'corrected_break_ends.*.after' => '休憩時間が勤務時間外です',
            'corrected_break_ends.*.before' => '休憩時間が勤務時間外です',
            'corrected_break_start_add.after' => '休憩時間が勤務時間外です',
            'corrected_break_start_add.before' => '休憩時間が勤務時間外です',
            'corrected_break_end_add.after' => '休憩時間が勤務時間外です',
            'corrected_break_end_add.before' => '休憩時間が勤務時間外です',
            'corrected_reason.required' => ':attributeを記入してください',
        ];
    }

    public function attributes()
    {
        return [
            'corrected_clock_in' => '出勤時間',
            'corrected_clock_out' => '退勤時間',
            'corrected_break_start' => '休憩開始時間',
            'corrected_break_end' => '休憩終了時間',
            'corrected_reason' => '備考',
        ];
    }
}
