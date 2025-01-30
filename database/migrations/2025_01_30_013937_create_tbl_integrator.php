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
        Schema::create('tbl_integrator', function (Blueprint $table) {
            $table->id();
            $table->string('ruas_id');
            $table->string('ruas_nama');
            $table->string('gerbang_id');
            $table->string('gerbang_nama');
            $table->string('host');
            $table->integer('port');
            $table->string('user');
            $table->string('pass');
            $table->string('database');
            $table->string('database_schema')->nullable();
            $table->enum('integrator', [1, 2, 3, 4]);
            $table->enum('tipe_gerbang', [1, 2, 3, 4]);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_integrator');
    }
};
