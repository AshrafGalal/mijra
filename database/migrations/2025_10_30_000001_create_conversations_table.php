<?php

use App\Models\Tenant\Customer;
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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->string('platform')->comment('whatsapp, facebook, instagram, etc.'); // ExternalPlatformEnum
            $table->string('platform_conversation_id')->nullable()->index()->comment('External platform conversation ID');
            $table->string('status')->default('new')->comment('new, open, pending, resolved, archived');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel_type')->nullable()->comment('direct, broadcast, group');
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('message_count')->default(0);
            $table->integer('unread_count')->default(0);
            $table->json('metadata')->nullable()->comment('Platform-specific data');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['customer_id', 'platform']);
            $table->index(['assigned_to', 'status']);
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

