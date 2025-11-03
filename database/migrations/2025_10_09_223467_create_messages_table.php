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
            $table->string('reply_to_story_id')->nullable();
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
            $table->index(['conversation_id', 'status']);
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
