<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
