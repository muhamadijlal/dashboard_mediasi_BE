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
        Schema::create('tbl_ruas', function (Blueprint $table) {
            $table->string('gerbang_id', 2);
            $table->string('ruas_id', 25);
            $table->string('gerbang_nama', 20);
            $table->string('host', 15);
            $table->string('port', 10);
            $table->string('user', 128);
            $table->string('pass', 128);
            $table->string('database', 128);
            $table->boolean('status')->default(false);
            $table->enum('integrator', [1, 2, 3, 4]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_ruas');
    }
};
