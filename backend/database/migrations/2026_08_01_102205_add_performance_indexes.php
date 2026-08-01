<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("CREATE INDEX idx_projects_active ON projects (status, created_at DESC) WHERE status NOT IN ('approved', 'rejected')");
            \Illuminate\Support\Facades\DB::statement("CREATE INDEX idx_notifications_unread ON notifications (notifiable_id, created_at DESC) WHERE read_at IS NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("DROP INDEX IF EXISTS idx_projects_active");
            \Illuminate\Support\Facades\DB::statement("DROP INDEX IF EXISTS idx_notifications_unread");
        }
    }
};
