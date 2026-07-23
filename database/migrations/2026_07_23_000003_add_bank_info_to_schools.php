<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('logo_path');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name', 100)->nullable()->after('bank_account_number');
            $table->string('bank_logo_path')->nullable()->after('bank_account_name');
            $table->text('payment_instructions')->nullable()->after('bank_logo_path');
        });
    }
    public function down(): void {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['bank_name','bank_account_number','bank_account_name','bank_logo_path','payment_instructions']);
        });
    }
};
