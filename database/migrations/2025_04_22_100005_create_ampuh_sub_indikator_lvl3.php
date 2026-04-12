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
        Schema::create('ampuh_sub_indikator_lvl3', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('gd_id');
            $table->string('sub_indikator_name');
            $table->integer('level_sub_indikator');
            $table->integer('rule_id');
            $table->integer('detil_rule_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ampuh_sub_indikator_lvl3');
    }
};
