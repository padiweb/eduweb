<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            // File soal/lampiran dari guru (opsional)
            $table->string('attachment_path')->nullable()->after('description');
        });

        // Tambah kolom feedback di submissions jika belum ada
        if (! Schema::hasColumn('assignment_submissions', 'teacher_comment')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->text('teacher_comment')->nullable()->after('feedback');
            });
        }
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('attachment_path');
        });
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn('teacher_comment');
        });
    }
};