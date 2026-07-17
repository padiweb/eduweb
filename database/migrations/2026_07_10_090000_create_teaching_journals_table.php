<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('journal_date');
            $table->tinyInteger('meeting_number')->default(1); // pertemuan ke-berapa
            $table->string('topic', 255);                    // materi/topik
            $table->text('description')->nullable();          // uraian kegiatan
            $table->enum('method', [
                'ceramah', 'diskusi', 'praktek', 'demonstrasi',
                'presentasi', 'tanya_jawab', 'lainnya'
            ])->default('ceramah');
            $table->tinyInteger('students_present')->default(0);   // siswa hadir
            $table->tinyInteger('students_absent')->default(0);    // siswa tidak hadir
            $table->string('photo_path')->nullable();              // foto kegiatan (opsional)
            $table->string('notes', 500)->nullable();              // catatan tambahan
            $table->boolean('is_reward_given')->default(false);    // sudah dapat poin?
            $table->timestamps();

            $table->unique(['schedule_id', 'journal_date']); // 1 jurnal per jadwal per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
