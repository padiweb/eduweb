<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // attendances - filter school+status paling sering
        $this->safeAddIndex('attendances', ['school_id', 'status'], 'idx_att_school_status');
        $this->safeAddIndex('attendances', ['student_id'], 'idx_att_student');

        // attendance_sessions - filter date
        $this->safeAddIndex('attendance_sessions', ['school_id', 'session_date'], 'idx_sess_school_date');
        $this->safeAddIndex('attendance_sessions', ['classroom_id', 'session_date'], 'idx_sess_classroom_date');

        // payment_bills - filter status
        $this->safeAddIndex('payment_bills', ['school_id', 'status'], 'idx_bill_school_status');
        $this->safeAddIndex('payment_bills', ['user_id', 'status'], 'idx_bill_user_status');

        // violations - filter school+student
        $this->safeAddIndex('violations', ['school_id', 'is_archived'], 'idx_viol_school');
        $this->safeAddIndex('violations', ['student_id', 'is_archived'], 'idx_viol_student');

        // assignment_submissions
        $this->safeAddIndex('assignment_submissions', ['assignment_id', 'student_id'], 'idx_sub_assignment');
        $this->safeAddIndex('assignment_submissions', ['student_id', 'status'], 'idx_sub_student');

        // users - login dan filter
        $this->safeAddIndex('users', ['school_id', 'role', 'is_active'], 'idx_usr_school_role');
    }

    public function down(): void
    {
        $indexes = [
            'attendances' => ['idx_att_school_status', 'idx_att_student'],
            'attendance_sessions' => ['idx_sess_school_date', 'idx_sess_classroom_date'],
            'payment_bills' => ['idx_bill_school_status', 'idx_bill_user_status'],
            'violations' => ['idx_viol_school', 'idx_viol_student'],
            'assignment_submissions' => ['idx_sub_assignment', 'idx_sub_student'],
            'users' => ['idx_usr_school_role'],
        ];
        foreach ($indexes as $table => $idxList) {
            foreach ($idxList as $idx) {
                try { Schema::table($table, fn($t) => $t->dropIndex($idx)); } catch (\Throwable) {}
            }
        }
    }

    private function safeAddIndex(string $table, array $cols, string $name): void
    {
        try {
            $exists = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$name]);
            if (empty($exists)) {
                Schema::table($table, fn($t) => $t->index($cols, $name));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Index $name skip: " . $e->getMessage());
        }
    }
};
