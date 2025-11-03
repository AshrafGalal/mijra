<?php

use App\Models\Landlord\Platform;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_platform_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            // OAuth related fields
            $table->string('user_access_token');
            $table->string('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Platform-specific identifiers
            $table->string('external_user_id')->nullable(); // Platform's user ID
            $table->string('external_account_id')->nullable(); // For platforms like Shopify (store ID)

            // Webhook related fields
            $table->string('webhook_id')->nullable();
            $table->string('webhook_secret')->nullable();


            // Additional data
            $table->json('credentials')->nullable(); // Store additional credentials (API keys, secrets)
            $table->json('meta')->nullable(); // Store platform-specific metadata
            $table->json('settings')->nullable(); // Store connection settings
            $table->unique(['tenant_id', 'platform_id'], 'tenant_platform_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_platforms');
    }
};
