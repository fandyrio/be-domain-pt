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
        Schema::create('ampuh_indikator', function (Blueprint $table) {
            $table->id();
            $table->string('gd_id');
            $table->string('indikator_name');
            $table->integer('level_sub_indikator')->default(0);
            $table->integer('last_upload_id')->default(null)->nullable();
            $table->integer('rule_id')->nullable();
            $table->integer('detil_rule_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ampuh_indikator');
    }
};
