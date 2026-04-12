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
        Schema::create('edoc_indikator', function (Blueprint $table) {
            $table->id();
            $table->integer('id_master');
            $table->text('edoc');
            $table->integer('periode');
            $table->string('timeline');
            $table->date('max_fill_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edoc_indikator');
    }
};
