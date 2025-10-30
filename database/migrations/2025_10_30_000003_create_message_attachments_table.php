<?php

use App\Models\Tenant\Message;
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
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Message::class)->constrained()->cascadeOnDelete();
            $table->string('type')->comment('image, video, audio, document, voice');
            $table->string('url')->comment('Local or remote URL');
            $table->string('platform_url')->nullable()->comment('Original platform URL');
            $table->string('mime_type')->nullable();
            $table->string('filename')->nullable();
            $table->bigInteger('file_size')->nullable()->comment('In bytes');
            $table->integer('width')->nullable()->comment('For images/videos');
            $table->integer('height')->nullable()->comment('For images/videos');
            $table->integer('duration')->nullable()->comment('For audio/video in seconds');
            $table->string('thumbnail_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['message_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};

