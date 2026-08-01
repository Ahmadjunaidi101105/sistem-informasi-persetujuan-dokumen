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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->integer('version')->default(1);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('project_id');
            $table->index('uploaded_by');
        });

        // PostgreSQL CHECK constraint for file size (max 10MB)
        DB::statement('ALTER TABLE project_documents ADD CONSTRAINT chk_docs_file_size CHECK (file_size > 0 AND file_size <= 10485760)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE project_documents DROP CONSTRAINT IF EXISTS chk_docs_file_size');
        Schema::dropIfExists('project_documents');
    }
};
