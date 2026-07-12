<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Override tarif per siswa — berbeda dari tarif kelas/jurusan
        // Ini bukan potongan (itu di student_discounts), tapi penggantian tarif penuh
        Schema::create('student_rate_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // Siswa
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');                                  // Tarif pengganti
            $table->string('reason')->nullable();                                  // Alasan override
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Satu siswa hanya boleh punya satu override per jenis per tahun ajaran
            $table->unique(['school_id', 'user_id', 'payment_type_id', 'academic_year_id'], 'unique_override');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_rate_overrides');
    }
};
