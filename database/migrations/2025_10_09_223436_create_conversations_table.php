<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('contact_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('external_identifier_id');
            $table->string('tenant_platform_id')->nullable()->comment('link to landlord.tenant_platform.id');
            $table->string('last_message_id')->nullable();
            $table->integer('unread_count')->default(0);
            $table->string('contact_identifier_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->tinyInteger('type')->default(\App\Enum\ConversationTypeEnum::INDIVIDUAL->value)->comment('individual = 1,group = 2');
            $table->tinyInteger('status')->default(\App\Enum\ConversationStatusEnum::OPEN->value);
            $table->timestamp('last_message_at')->nullable();
            $table->string('platform_account_id')->nullable()->comment('link to landlord.whatsapp_accounts.id');
            $table->string('platform_account_number')->nullable()->comment('phone of receiver used to send message from it');
            $table->string('platform');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
