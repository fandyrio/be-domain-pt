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
        Schema::create('msg_sent', function (Blueprint $table) {
            $table->id();
            $table->text('msg');
            $table->boolean('status');
            $table->string('nama_penerima');
            $table->string('no_penerima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msg_sent');
    }
};
