<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE assignment_submissions 
            MODIFY COLUMN status 
            ENUM('submitted','late','graded','not_submitted') 
            NOT NULL DEFAULT 'submitted'");
        DB::statement("ALTER TABLE assignment_submissions 
            MODIFY COLUMN submitted_at DATETIME NULL");
    }
    public function down(): void {
        DB::table('assignment_submissions')->where('status','not_submitted')->delete();
        DB::statement("ALTER TABLE assignment_submissions 
            MODIFY COLUMN status 
            ENUM('submitted','late','graded') 
            NOT NULL DEFAULT 'submitted'");
    }
};
