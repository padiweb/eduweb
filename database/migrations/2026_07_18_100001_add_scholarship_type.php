<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah scholarship_type ke student_discounts
        // cash    = dana uang (PIP, CSR) → masuk pemasukan
        // waiver  = potongan tagihan     → tidak masuk pemasukan
        if (Schema::hasTable('student_discounts') && !Schema::hasColumn('student_discounts', 'scholarship_type')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->enum('scholarship_type', ['cash', 'waiver'])->default('cash')->after('discount_value');
            });
        }

        // Tambah scholarship_type ke discount_programs juga
        if (Schema::hasTable('discount_programs') && !Schema::hasColumn('discount_programs', 'scholarship_type')) {
            Schema::table('discount_programs', function (Blueprint $table) {
                $table->enum('scholarship_type', ['cash', 'waiver'])->default('cash')->after('default_value');
            });
        }

        // Update enum channel di payment_transactions
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN channel ENUM('cash','transfer','scholarship','scholarship_cash','scholarship_waiver') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_discounts', 'scholarship_type')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->dropColumn('scholarship_type');
            });
        }
        if (Schema::hasColumn('discount_programs', 'scholarship_type')) {
            Schema::table('discount_programs', function (Blueprint $table) {
                $table->dropColumn('scholarship_type');
            });
        }
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN channel ENUM('cash','transfer','scholarship') NOT NULL");
    }
};
