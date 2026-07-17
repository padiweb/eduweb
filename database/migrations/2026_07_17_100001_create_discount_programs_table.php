<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel discount_programs jika belum ada
        if (!Schema::hasTable('discount_programs')) {
            Schema::create('discount_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_type_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('code', 30)->nullable();
                $table->enum('discount_type', ['percent', 'fixed']);
                $table->unsignedBigInteger('default_value');
                $table->date('valid_from');
                $table->date('valid_until')->nullable();
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        // Buat tabel discount_program_members jika belum ada
        if (!Schema::hasTable('discount_program_members')) {
            Schema::create('discount_program_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discount_program_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('override_value')->nullable();
                $table->string('notes')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();

                $table->unique(['discount_program_id', 'user_id'], 'unique_program_member');
            });
        }

        // Tambah kolom discount_program_id ke student_discounts jika belum ada
        if (Schema::hasTable('student_discounts') && !Schema::hasColumn('student_discounts', 'discount_program_id')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->foreignId('discount_program_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_discounts') && Schema::hasColumn('student_discounts', 'discount_program_id')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('discount_program_id');
            });
        }
        Schema::dropIfExists('discount_program_members');
        Schema::dropIfExists('discount_programs');
    }
};
