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
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE projects ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (to_tsvector('indonesian', coalesce(title, '') || ' ' || coalesce(description, '') || ' ' || coalesce(project_code, ''))) STORED");
            \Illuminate\Support\Facades\DB::statement("CREATE INDEX idx_projects_search ON projects USING GIN(search_vector)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("DROP INDEX IF EXISTS idx_projects_search");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE projects DROP COLUMN search_vector");
        }
    }
};
