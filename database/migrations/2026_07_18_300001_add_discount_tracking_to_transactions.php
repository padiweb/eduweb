<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom student_discount_id untuk track beasiswa yang dipakai per transaksi
        if (Schema::hasTable('payment_transactions') && !Schema::hasColumn('payment_transactions', 'student_discount_id')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->foreignId('student_discount_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        // Pastikan enum channel sudah include scholarship_waiver dan scholarship_cash
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN channel ENUM('cash','transfer','scholarship','scholarship_cash','scholarship_waiver') NOT NULL");

        // Tambah scholarship_type ke discount_programs jika belum ada
        if (Schema::hasTable('discount_programs') && !Schema::hasColumn('discount_programs', 'scholarship_type')) {
            Schema::table('discount_programs', function (Blueprint $table) {
                $table->enum('scholarship_type', ['cash', 'waiver'])->default('cash');
            });
        }

        // Tambah scholarship_type ke student_discounts jika belum ada
        if (Schema::hasTable('student_discounts') && !Schema::hasColumn('student_discounts', 'scholarship_type')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->enum('scholarship_type', ['cash', 'waiver'])->default('cash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_transactions', 'student_discount_id')) {
            Schema::table('payment_transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('student_discount_id');
            });
        }
    }
};
