<?php

use App\Models\Tenant\User;
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
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('shortcut')->nullable()->comment('Keyboard shortcut like /hello');
            $table->text('content');
            $table->string('category')->nullable()->comment('greeting, closing, faq, etc.');
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete()->comment('NULL for team-shared');
            $table->boolean('is_shared')->default(false)->comment('Shared with team');
            $table->json('platforms')->nullable()->comment('Specific platforms: ["whatsapp", "facebook"]');
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_shared']);
            $table->index('shortcut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
    }
};

