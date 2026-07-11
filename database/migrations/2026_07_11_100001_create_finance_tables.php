<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Sumber Dana ─────────────────────────────────────────────────────
        // Master sumber dana: BOS, BOSDA, Kas Siswa, Donasi, dll
        Schema::create('fund_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');                              // BOS, BOSDA, Kas Sekolah, dll
            $table->string('code', 20)->nullable();             // Kode singkat: BOS, BOSDA, KAS
            $table->enum('type', ['siswa', 'bos', 'bosda', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 2. Pemasukan Dana ─────────────────────────────────────────────────
        // Pencairan BOS/BOSDA, setoran dari siswa (otomatis dari payment), dll
        Schema::create('fund_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fund_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 50)->nullable();  // No. SK BOS, No. kuitansi
            $table->string('description');
            $table->unsignedBigInteger('amount');               // Selalu integer (rupiah)
            $table->date('income_date');
            $table->string('period_label', 50)->nullable();     // "Triwulan I 2026"
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();       // Scan SK/bukti pencairan
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // ── 3. Kategori Pengeluaran ───────────────────────────────────────────
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');                              // Penggajian, Kegiatan, ATK, dll
            $table->string('code', 20)->nullable();
            $table->enum('type', ['payroll', 'activity', 'operational', 'other'])->default('other');
            $table->boolean('requires_approval')->default(false); // Kategori ini wajib approval?
            $table->unsignedBigInteger('approval_threshold')->default(0); // Minimal nominal untuk approval (0 = semua)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 4. Pengeluaran ────────────────────────────────────────────────────
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fund_source_id')->constrained();  // Dana dari mana
            $table->foreignId('expense_category_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->string('reference_number', 50)->nullable();
            $table->string('description');
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->string('period_label', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();       // Bukti pengeluaran/nota
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'cancelled'])
                  ->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();                               // Tidak bisa hapus, hanya soft delete
        });

        // ── 5. Log Approval Pengeluaran ───────────────────────────────────────
        Schema::create('expense_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();         // Siapa yang approve/tolak
            $table->enum('action', ['submitted', 'approved', 'rejected', 'revised']);
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // ── 6. Master Gaji Guru/Staff ─────────────────────────────────────────
        Schema::create('salary_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Guru/staff
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('base_salary')->default(0);       // Gaji pokok
            $table->unsignedBigInteger('jp_rate')->default(0);           // Tarif per JP
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['school_id', 'user_id', 'academic_year_id']); // 1 config per tahun ajaran
        });

        // ── 7. Komponen Tunjangan Jabatan ─────────────────────────────────────
        Schema::create('salary_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_config_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');                              // "Wali Kelas", "Ketua PPDB", dll
            $table->unsignedBigInteger('amount');               // Nominal tunjangan
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── 8. Slip Gaji ──────────────────────────────────────────────────────
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // Guru/staff
            $table->foreignId('expense_id')->nullable()->constrained();      // Link ke pengeluaran
            $table->foreignId('academic_year_id')->constrained();
            $table->string('period_label', 50);                  // "Juli 2026"
            $table->date('period_date');
            $table->unsignedBigInteger('base_salary');
            $table->integer('jp_count')->default(0);             // Jumlah JP bulan ini
            $table->unsignedBigInteger('jp_rate')->default(0);
            $table->unsignedBigInteger('jp_total')->default(0);  // JP × tarif
            $table->unsignedBigInteger('allowances_total')->default(0); // Total tunjangan jabatan
            $table->unsignedBigInteger('deductions')->default(0);       // Potongan
            $table->unsignedBigInteger('gross_salary');          // Total kotor
            $table->unsignedBigInteger('net_salary');            // Total bersih (dikurangi potongan)
            $table->json('allowances_detail')->nullable();       // Detail tunjangan jabatan
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['school_id', 'user_id', 'period_date']); // 1 slip per bulan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('salary_allowances');
        Schema::dropIfExists('salary_configs');
        Schema::dropIfExists('expense_approvals');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('fund_incomes');
        Schema::dropIfExists('fund_sources');
    }
};
