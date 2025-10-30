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
        Schema::create('message_status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Message::class)->constrained()->cascadeOnDelete();
            $table->string('status')->comment('sent, delivered, read, failed');
            $table->timestamp('status_at');
            $table->json('metadata')->nullable()->comment('Platform response data');
            $table->timestamps();
            
            $table->index(['message_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_status_updates');
    }
};

