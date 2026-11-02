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
        Schema::create('perbaikan_checklist', function (Blueprint $table) {
            $table->id();
            $table->integer('level');
            $table->integer('periode_perbaikan_id');
            $table->integer('indikator_lvl_id');
            $table->text('catatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perbaikan_checklist');
    }
};
