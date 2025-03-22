<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectedBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corrected_break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corrected_attendance_id')->constrained('attendances')->cascadeOnUpdate()->cascadeOnDelete();
            $table->time('corrected_break_start');
            $table->time('corrected_break_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corrected_break_times');
    }
}
