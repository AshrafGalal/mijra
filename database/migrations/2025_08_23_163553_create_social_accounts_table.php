<?php

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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('provider_name');
            $table->string('platform_account_id')->index();
            $table->string('account_name');
            $table->string('phone_number')->nullable();
            $table->string('username', 300);
            $table->string('refresh_token', 300);
            $table->timestamp('token_expires_at')->nullable();
            $table->string('webhook_verify_token')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['platform_account_id', 'provider_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
