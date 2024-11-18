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
    
        Schema::table('guardians', function (Blueprint $table) {
            $table->dropForeign('guardians_import_batch_id_foreign');
            $table->dropColumn('import_batch_id');
            $table->foreignId('import_batch_id')->constrained('import_batches')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            //
        });
    }
};
