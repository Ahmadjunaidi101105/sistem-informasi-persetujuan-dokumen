<?php

declare(strict_types=1);

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
        Schema::create('project_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->string('status_from', 20);
            $table->string('status_to', 20);
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();

            $table->index('project_id');
            $table->index('reviewer_id');
            $table->index(['project_id', 'status_to']);
            $table->index(['reviewer_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reviews');
    }
};
