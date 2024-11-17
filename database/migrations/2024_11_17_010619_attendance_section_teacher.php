<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_section_teacher', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('student_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('section_id')->constrained()->onDelete('cascade'); 
            $table->date('date'); 
            $table->boolean('present');
            $table->time('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_section_teacher');
    }
};
