<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('data_source_id')->nullable()->constrained('client_data_sources');
            
            $table->enum('category', ['growth', 'risk', 'opportunity', 'anomaly', 'prediction']);
            $table->string('title');
            $table->text('description');
            $table->text('recommendation')->nullable();
            $table->json('evidence')->nullable();
            $table->decimal('confidence_score', 3, 2)->default(0.0);
            $table->enum('impact_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['new', 'viewed', 'dismissed', 'actioned', 'archived'])->default('new');
            
            $table->timestamp('viewed_at')->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('clients');
            $table->text('action_notes')->nullable();
            
            // FIX: Make generated_at nullable or use current timestamp as default
            $table->timestamp('generated_at')->nullable(); // Option 1: Nullable
            // OR
            // $table->timestamp('generated_at')->useCurrent(); // Option 2: Default to now
            
            $table->timestamps();

            $table->index(['client_id', 'status', 'generated_at']);
            $table->index(['category', 'impact_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_insights');
    }
};