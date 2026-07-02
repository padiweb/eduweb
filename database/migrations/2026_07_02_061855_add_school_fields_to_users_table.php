<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('username', 50)->unique()->nullable()->after('name');
            $table->string('nis', 20)->nullable()->after('username');
            $table->string('nip', 30)->nullable()->after('nis');
            $table->enum('role', ['siswa', 'guru', 'kesiswaan', 'wali_kelas', 'admin'])->after('nip')->default('siswa');
            $table->string('avatar_path')->nullable()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->tinyInteger('failed_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('locked_until')->nullable()->after('failed_attempts');
            $table->boolean('is_active')->default(true)->after('locked_until');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn([
                'username', 'nis', 'nip', 'role', 'avatar_path',
                'last_login_at', 'last_login_ip', 'failed_attempts',
                'locked_until', 'is_active', 'deleted_at',
            ]);
        });
    }
};