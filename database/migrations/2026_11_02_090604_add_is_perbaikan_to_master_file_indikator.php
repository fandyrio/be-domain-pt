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
        Schema::table('master_file_indikator', function (Blueprint $table) {
            $table->boolean('is_perbaikan')->default(false)->after('gd_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_file_indikator', function (Blueprint $table) {
            //
        });
    }
};
