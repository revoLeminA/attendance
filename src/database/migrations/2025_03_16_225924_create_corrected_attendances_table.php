<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectedAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corrected_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->date('corrected_date');
            $table->time('corrected_clock_in');
            $table->time('corrected_clock_out');
            $table->string('corrected_reason');
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
        Schema::dropIfExists('corrected_attendances');
    }
}
