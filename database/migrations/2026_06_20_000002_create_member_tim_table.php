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
        Schema::create('member_tim', function (Blueprint $table) {
            $table->id('id_member');
            $table->foreignId('id_tim')->constrained('tim', 'id_tim')->cascadeOnDelete();
            $table->string('nama_peserta');
            $table->string('nim_peserta')->unique();
            $table->enum('role', ['ketua', 'anggota']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_tim');
    }
};
