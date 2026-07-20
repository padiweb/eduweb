<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prakerin_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('prakerin_placements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('journal_date');
            $table->text('content');                              // Laporan tertulis
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();

            // Catatan guru pembimbing
            $table->text('teacher_note')->nullable();
            $table->foreignId('noted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('noted_at')->nullable();

            $table->timestamps();

            $table->unique(['placement_id', 'journal_date']);
            $table->index(['student_id', 'journal_date']);
        });

        Schema::create('prakerin_journal_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('prakerin_journals')->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption', 200)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prakerin_journal_photos');
        Schema::dropIfExists('prakerin_journals');
    }
};
