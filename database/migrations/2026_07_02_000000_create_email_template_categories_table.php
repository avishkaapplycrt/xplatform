<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->string('color', 7)->default('#374151');
            $table->string('bg_color', 7)->default('#f3f4f6');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['client_id', 'slug']);
        });

        // Seed default categories
        $defaultCategories = [
            ['name' => 'General', 'slug' => 'general', 'color' => '#374151', 'bg_color' => '#f3f4f6', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Intervention', 'slug' => 'intervention', 'color' => '#92400e', 'bg_color' => '#fef3c7', 'sort_order' => 2, 'is_default' => true],
            ['name' => 'Win-Back', 'slug' => 'winback', 'color' => '#991b1b', 'bg_color' => '#fee2e2', 'sort_order' => 3, 'is_default' => true],
            ['name' => 'Upsell', 'slug' => 'upsell', 'color' => '#166534', 'bg_color' => '#dcfce7', 'sort_order' => 4, 'is_default' => true],
            ['name' => 'Onboarding', 'slug' => 'onboarding', 'color' => '#1e40af', 'bg_color' => '#dbeafe', 'sort_order' => 5, 'is_default' => true],
        ];

        foreach ($defaultCategories as $cat) {
            \DB::table('email_template_categories')->insert(array_merge($cat, [
                'client_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_categories');
    }
};