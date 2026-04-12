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
        Schema::table('ampuh_sub_indikator_lvl2', function (Blueprint $table) {
            $table->integer('last_upload_id')->after('level_sub_indikator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_indikator_lvl2', function (Blueprint $table) {
            //
        });
    }
};
