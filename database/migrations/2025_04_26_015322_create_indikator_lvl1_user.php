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
        Schema::create('indikator_lvl1_user', function (Blueprint $table) {
            $table->id();
            $table->integer('id_indikator_lvl1');
            $table->integer('id_bagian');
            $table->boolean('has_child');
            $table->integer('periode');
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_lvl1_user');
    }
};
