<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'scholarship' ke enum channel di payment_transactions
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN channel ENUM('cash','transfer','scholarship') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN channel ENUM('cash','transfer') NOT NULL");
    }
};
