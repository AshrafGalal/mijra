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
        Schema::create('automated_replies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type')->comment('keyword, greeting, away, fallback');
            $table->json('keywords')->nullable()->comment('Array of keywords to match');
            $table->text('reply_message');
            $table->string('reply_type')->default('text')->comment('text, template, buttons');
            $table->json('reply_metadata')->nullable()->comment('Buttons, quick_replies, etc.');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Higher priority rules match first');
            $table->json('conditions')->nullable()->comment('Additional conditions (time, platform, etc.)');
            $table->timestamps();
            
            $table->index(['trigger_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automated_replies');
    }
};

