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
        Schema::create('tenant_platform_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('platforms')->cascadeOnDelete();
            $table->foreignId('tenant_platform_connection_id')
                ->constrained('tenant_platform_connections')
                ->cascadeOnDelete();

            $table->string('channel_type')->nullable(); // page, shop, business_account, etc.
            $table->string('external_id'); // Platform's channel ID
            $table->string('name');

            // Access credentials
            $table->string('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Channel details
            $table->string('category')->nullable();
            $table->json('category_list')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            // Status tracking
            $table->tinyInteger('status')
                ->default(\App\Enum\ChannelStatusEnum::ACTIVE->value);

            $table->unique(['tenant_id', 'platform_id', 'external_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_platform_channels');
    }
};
