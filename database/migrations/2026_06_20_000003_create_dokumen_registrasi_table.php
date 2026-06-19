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
        Schema::create('dokumen_registrasi', function (Blueprint $table) {
            $table->id('id_registrasi');
            $table->foreignId('id_tim')->constrained('tim', 'id_tim')->cascadeOnDelete();
            $table->string('link_file_registrasi')->nullable();
            $table->enum('status_registrasi', ['pending', 'ditolak', 'berhasil'])->default('pending');
            $table->text('catatan_registrasi')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_registrasi');
    }
};
