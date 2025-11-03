<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Role;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RefershConversationAndMessagesTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            $table->string('platform_account_id')->nullable()->comment('link to landlord.whatsapp_accounts.id,or any external identifier');
            $table->string('platform_account_number')->nullable()->comment('phone of receiver used to send message from it');
            $table->string('platform');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('external_message_id')->nullable()->unique();
            $table->string('message_type')->comment('text,image,audio,video,file,location,contact,sticker,template');
            $table->string('direction')->default(\App\Enum\MessageDirectionEnum::INCOMING->value)->comment('(incoming,outgoing)');
            $table->tinyInteger('status')->default(\App\Enum\MessageStatusEnum::PENDING->value)->comment('(received,sent,delivered,failed,read');
            $table->string('sender')->nullable();
            $table->string('receiver')->nullable();
            $table->boolean('is_view_once')->default(false);
            $table->text('body')->nullable();
            $table->boolean('has_media')->default(false);
            $table->foreignUuid('reply_to_message_id')->nullable()->constrained('messages');
            $table->string('reply_to_external_message_id')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('emoji', 10)->nullable(); // Emoji character(s)
            $table->softDeletes();
            $table->string('platform_account_id')->nullable();
            $table->boolean('is_forward')->default(false);
            $table->timestamps();
            $table->index('external_message_id');
            $table->index(['conversation_id']);
        });
    }
}
