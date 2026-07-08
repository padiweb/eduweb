<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('nisn', 10)->nullable()->unique()->after('nis');
            $table->string('niy', 30)->nullable()->after('nip');  // untuk guru honorer
            $table->string('phone', 20)->nullable()->after('niy');
            // Tambah role baru
            $table->enum('role', [
                'siswa', 'guru', 'wali_kelas',
                'kesiswaan', 'admin', 'bendahara', 'ortu'
            ])->default('siswa')->change();
        });

        // Tabel jabatan (dibuat admin, bisa rangkap)
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);  // cth: Kepala Sekolah, Wali Kelas, BP/BK
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        // Pivot: guru bisa punya banyak jabatan
        Schema::create('teacher_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'position_id']);
        });

        // Detail data guru
        Schema::create('teacher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->enum('religion', ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'])->nullable();
            $table->enum('employment_status', ['ASN','PPPK','Kontrak','Honor','GTY'])->nullable();
            $table->enum('marital_status', ['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'])->nullable();
            $table->tinyInteger('children_count')->default(0);
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });

        // Detail data siswa
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->enum('religion', ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'])->nullable();
            $table->string('nik', 16)->nullable();       // NIK siswa
            $table->string('no_kk', 16)->nullable();     // Nomor Kartu Keluarga
            $table->string('whatsapp', 20)->nullable();  // WA siswa

            // Data orang tua
            $table->string('father_name', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('parent_whatsapp', 20)->nullable();

            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_details');
        Schema::dropIfExists('teacher_details');
        Schema::dropIfExists('teacher_positions');
        Schema::dropIfExists('positions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'niy', 'phone']);
            $table->enum('role', ['siswa','guru','wali_kelas','kesiswaan','admin'])->default('siswa')->change();
        });
    }
};
