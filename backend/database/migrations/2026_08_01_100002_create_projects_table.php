<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_category_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('priority', 10)->default('normal');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->integer('revision_count')->default(0);
            $table->foreignId('current_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Single column indexes
            $table->index('status');
            $table->index('created_at');
            $table->index('submitted_at');

            // Composite indexes
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        // PostgreSQL CHECK constraints
        DB::statement("ALTER TABLE projects ADD CONSTRAINT chk_projects_status CHECK (status IN ('draft', 'submitted', 'in_review', 'approved', 'revised', 'rejected'))");
        DB::statement("ALTER TABLE projects ADD CONSTRAINT chk_projects_priority CHECK (priority IN ('low', 'normal', 'high'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS chk_projects_status');
        DB::statement('ALTER TABLE projects DROP CONSTRAINT IF EXISTS chk_projects_priority');
        Schema::dropIfExists('projects');
    }
};
