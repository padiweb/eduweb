<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel tugas
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            // Metode pengumpulan: file, text, link, any
            $table->enum('submission_type', ['file', 'text', 'link', 'any'])->default('any');

            // Deadline
            $table->dateTime('deadline')->nullable();
            $table->boolean('is_closed')->default(false); // guru tutup manual
            $table->dateTime('closed_at')->nullable();

            // Nilai max
            $table->tinyInteger('max_score')->default(100);

            $table->timestamps();
        });

        // Tabel pengumpulan tugas siswa
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            // Konten pengumpulan
            $table->text('content')->nullable();       // teks
            $table->string('file_path')->nullable();   // file upload
            $table->string('link_url')->nullable();    // link

            // Status
            $table->enum('status', ['submitted', 'late', 'graded'])->default('submitted');
            $table->tinyInteger('score')->nullable();  // 0-100
            $table->text('feedback')->nullable();      // catatan guru

            // Pelanggaran
            $table->boolean('violation_created')->default(false);

            $table->dateTime('submitted_at');
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
