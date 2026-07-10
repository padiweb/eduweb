<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Jenis pembayaran ─────────────────────────────────────────────
        if (!Schema::hasTable('payment_types')) {
            Schema::create('payment_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name');                        // "SPP", "Ujian PAS", dll
                $table->string('code', 20)->nullable();        // Kode singkat opsional
                $table->enum('category', [
                    'spp',        // SPP bulanan / semester
                    'ujian',      // PAS, PAT, US
                    'kegiatan',   // Study tour, OSIS
                    'seragam',    // Seragam, buku
                    'lainnya',    // Bebas input
                ]);
                $table->enum('period_type', [
                    'monthly',    // SPP bulanan
                    'semester',   // Per semester
                    'once',       // Sekali bayar (ujian, kegiatan)
                ]);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // ── 2. Tarif per jenis + kelas/jurusan ────────────────────────────
        if (!Schema::hasTable('payment_rates')) {
            Schema::create('payment_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete(); // null = berlaku semua kelas
                $table->foreignId('major_id')->nullable()->constrained()->nullOnDelete();     // null = semua jurusan
                $table->unsignedBigInteger('amount');          // Nominal dalam RUPIAH (integer)
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Satu tarif per kombinasi type+tahun+kelas+jurusan
                $table->unique(['payment_type_id', 'academic_year_id', 'classroom_id', 'major_id'], 'unique_rate');
            });
        }

        // ── 3. Keringanan / beasiswa per siswa ────────────────────────────
        if (!Schema::hasTable('student_discounts')) {
            Schema::create('student_discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // Siswa
                $table->foreignId('payment_type_id')->nullable()->constrained()->nullOnDelete(); // null = berlaku semua jenis
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->string('name');                        // "Beasiswa Prestasi", "Keringanan Dhuafa"
                $table->enum('discount_type', ['percent', 'fixed']); // persen atau nominal tetap
                $table->unsignedBigInteger('discount_value'); // Nilai persen (0-100) atau nominal (rupiah)
                $table->date('valid_from');
                $table->date('valid_until')->nullable();
                $table->string('notes')->nullable();
                // Siapa yang beri keringanan (audit)
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        // ── 4. Tagihan per siswa per periode ──────────────────────────────
        if (!Schema::hasTable('payment_bills')) {
            Schema::create('payment_bills', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();          // Siswa
                $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_rate_id')->nullable()->constrained()->nullOnDelete();
                $table->string('period_label');                // "Juli 2026", "Semester 1 2025/2026"
                $table->date('period_date');                   // Untuk sorting: tanggal awal periode
                $table->unsignedBigInteger('amount_base');     // Tarif asli sebelum diskon
                $table->unsignedBigInteger('amount_discount'); // Total potongan
                $table->unsignedBigInteger('amount_billed');   // Yang harus dibayar (base - discount)
                $table->unsignedBigInteger('amount_paid');     // Total sudah terbayar
                $table->enum('status', [
                    'unpaid',     // Belum bayar
                    'partial',    // Cicilan sebagian
                    'paid',       // Lunas
                    'waived',     // Dibebaskan (beasiswa penuh / keputusan kepsek)
                ])->default('unpaid');
                $table->date('due_date')->nullable();
                // Siapa yang buat tagihan (audit)
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();

                // Satu tagihan per siswa per jenis per periode
                $table->unique(['user_id', 'payment_type_id', 'academic_year_id', 'period_date'], 'unique_bill');
            });
        }

        // ── 5. Jadwal cicilan per tagihan ─────────────────────────────────
        if (!Schema::hasTable('payment_installments')) {
            Schema::create('payment_installments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_bill_id')->constrained()->cascadeOnDelete();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('installment_number'); // Ke-1, ke-2, dst
                $table->unsignedBigInteger('amount_due');          // Jumlah cicilan ini
                $table->unsignedBigInteger('amount_paid')->default(0);
                $table->date('due_date');
                $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
                $table->timestamps();
            });
        }

        // ── 6. Transaksi pembayaran ────────────────────────────────────────
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_bill_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_installment_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siswa pemilik tagihan
                $table->string('reference_number', 50)->unique(); // Nomor referensi unik (auto-generate)
                $table->unsignedBigInteger('amount');              // Jumlah yang dibayar kali ini
                $table->enum('channel', [
                    'cash',      // Tunai di sekolah
                    'transfer',  // Transfer bank + upload bukti
                ]);
                $table->enum('status', [
                    'pending',   // Upload bukti, belum dikonfirmasi
                    'approved',  // Dikonfirmasi bendahara
                    'rejected',  // Ditolak (bukti tidak valid)
                    'cancelled', // Dibatalkan dengan alasan
                ])->default('pending');

                // Untuk transfer: bukti upload
                $table->string('receipt_path')->nullable();       // Path file bukti (private storage)
                $table->string('bank_name', 100)->nullable();     // Nama bank pengirim
                $table->string('sender_name', 150)->nullable();   // Nama pengirim
                $table->date('transfer_date')->nullable();        // Tanggal transfer
                $table->text('notes')->nullable();                // Catatan dari ortu/siswa

                // Untuk cash: langsung approved
                $table->text('cashier_notes')->nullable();        // Catatan bendahara saat input tunai

                // Konfirmasi
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->text('rejection_reason')->nullable();     // Alasan tolak/batal
                $table->text('cancellation_reason')->nullable();

                // Siapa yang input (ortu upload sendiri atau bendahara input tunai)
                $table->foreignId('created_by')->constrained('users');
                $table->string('created_by_ip', 45)->nullable();  // IP address untuk audit

                $table->timestamps();
            });
        }

        // ── 7. Audit log keuangan (IMMUTABLE - tidak boleh dihapus/edit) ──
        if (!Schema::hasTable('payment_audit_logs')) {
            Schema::create('payment_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siapa yang melakukan aksi
                $table->string('action', 50);          // 'bill_created', 'bill_waived', 'txn_approved', dll
                $table->string('target_type', 50);     // 'PaymentBill', 'PaymentTransaction', dll
                $table->unsignedBigInteger('target_id');
                $table->json('old_values')->nullable(); // State sebelum perubahan
                $table->json('new_values')->nullable(); // State sesudah perubahan
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at');        // Hanya created_at, tidak ada updated_at
            });
        }
    }

    public function down(): void
    {
        // Urutan terbalik karena foreign key
        Schema::dropIfExists('payment_audit_logs');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_installments');
        Schema::dropIfExists('payment_bills');
        Schema::dropIfExists('student_discounts');
        Schema::dropIfExists('payment_rates');
        Schema::dropIfExists('payment_types');
    }
};
