<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setoran kas: rekap harian uang tunai + transfer yang disetorkan ke kas bank
        Schema::create('kas_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fund_source_id')->constrained()->cascadeOnDelete(); // Kas tujuan (bank/rekening)
            $table->date('tanggal_setoran');
            $table->unsignedBigInteger('total_tunai')->default(0);   // Total kas tunai dari siswa
            $table->unsignedBigInteger('total_transfer')->default(0); // Total transfer yang dikonfirmasi
            $table->unsignedBigInteger('total_setoran');             // Jumlah yang disetor ke bank
            $table->string('keterangan')->nullable();
            $table->string('no_referensi')->nullable();              // No. slip setoran bank
            $table->enum('status', ['draft', 'setor'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('disetor_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_setorans');
    }
};
