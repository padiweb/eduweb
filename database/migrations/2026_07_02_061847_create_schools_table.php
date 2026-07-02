<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('npsn', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->smallInteger('attendance_radius_meters')->default(200);
            $table->time('school_start_time')->default('07:00:00');
            $table->time('late_threshold_time')->default('07:15:00');
            $table->boolean('feature_attendance')->default(true);
            $table->boolean('feature_assignments')->default(false);
            $table->boolean('feature_grades')->default(false);
            $table->boolean('feature_violations')->default(false);
            $table->boolean('feature_journal')->default(false);
            $table->boolean('feature_prakerin')->default(false);
            $table->boolean('feature_payment_info')->default(false);
            $table->boolean('feature_cbt_integration')->default(false);
            $table->enum('package', ['starter', 'pro', 'enterprise'])->default('starter');
            $table->date('active_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};