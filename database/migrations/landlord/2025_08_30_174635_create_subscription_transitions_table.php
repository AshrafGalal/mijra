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
        Schema::create('subscription_transitions', function (Blueprint $table) {
            // Relations
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('old_subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignUuid('new_subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('old_plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('new_plan_id')->constrained('plans')->cascadeOnDelete();

            // Change details
            $table->tinyInteger('change_type');
            $table->dateTime('effective_date');
            $table->decimal('proration_amount', 12, 2)->default(0);
            $table->string('reason')->nullable();

            // Store additional data as JSON
            $table->json('metadata')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_transitions');
    }
};
