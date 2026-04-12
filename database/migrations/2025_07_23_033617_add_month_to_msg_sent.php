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
        Schema::table('msg_sent', function (Blueprint $table) {
            $table->integer('month')->after('no_penerima');
            $table->integer('year')->after('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('msg_sent', function (Blueprint $table) {
            //
        });
    }
};
