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
        Schema::create('conversation_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#3B82F6');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique('name');
        });
        
        // Pivot table
        Schema::create('conversation_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_tag_id')->constrained('conversation_tags')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['conversation_id', 'conversation_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_tag');
        Schema::dropIfExists('conversation_tags');
    }
};

