<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel kelompok mata pelajaran — dibuat admin, fleksibel per sekolah
        Schema::create('subject_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);        // cth: Kelompok A, Muatan Lokal, Wajib Umum
            $table->string('code', 20)->nullable(); // cth: A, B, C1, Mulok
            $table->text('description')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        // Update tabel subjects: ganti kolom category (enum hardcode) dengan group_id
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('subject_group_id')
                ->nullable()
                ->after('major_id')
                ->constrained('subject_groups')
                ->nullOnDelete();

            // Hapus kolom category yang hardcode
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_group_id');
            $table->enum('category', ['A', 'B', 'C1', 'C2', 'C3'])->after('code')->default('A');
        });

        Schema::dropIfExists('subject_groups');
    }
};
