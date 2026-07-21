<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prakerin_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained('prakerin_placements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('absence_date');
            $table->enum('type', ['izin', 'sakit', 'libur']);
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['placement_id', 'absence_date']);
            $table->index(['student_id', 'absence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prakerin_absences');
    }
};
