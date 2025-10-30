<?php

use App\Models\Tenant\Conversation;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Conversation::class)->constrained()->cascadeOnDelete();
            $table->string('platform_message_id')->nullable()->index()->comment('External platform message ID');
            $table->string('direction')->comment('inbound, outbound');
            $table->string('type')->default('text')->comment('text, image, video, audio, document, location, contact, template');
            $table->text('content')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete()->comment('User who sent (for outbound)');
            $table->string('sender_type')->nullable()->comment('customer, user, system');
            $table->string('status')->nullable()->comment('sent, delivered, read, failed');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable()->comment('Platform-specific data (buttons, quick_replies, etc.)');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['conversation_id', 'created_at']);
            $table->index('platform_message_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};



