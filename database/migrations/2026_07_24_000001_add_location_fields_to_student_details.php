<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_details', function (Blueprint $table) {
            $table->string('province', 100)->nullable()->after('address');
            $table->string('regency', 100)->nullable()->after('province');   // kab/kota
            $table->string('district', 100)->nullable()->after('regency');   // kecamatan
            $table->string('village', 100)->nullable()->after('district');   // kelurahan/desa
            $table->string('street', 255)->nullable()->after('village');     // nama jalan/dusun/RT
            $table->boolean('is_abroad')->default(false)->after('street');   // tinggal di luar negeri
            $table->string('country', 100)->nullable()->after('is_abroad');  // negara jika abroad
        });
    }

    public function down(): void
    {
        Schema::table('student_details', function (Blueprint $table) {
            $table->dropColumn(['province','regency','district','village','street','is_abroad','country']);
        });
    }
};
