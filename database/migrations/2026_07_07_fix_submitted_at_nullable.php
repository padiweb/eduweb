<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // submitted_at harus nullable untuk siswa yang tidak mengumpulkan
            $table->dateTime('submitted_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dateTime('submitted_at')->nullable(false)->change();
        });
    }
};