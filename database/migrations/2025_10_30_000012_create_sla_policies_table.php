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
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('first_response_time_minutes')->comment('Target time for first response');
            $table->integer('resolution_time_hours')->comment('Target time for resolution');
            $table->json('conditions')->nullable()->comment('When this SLA applies (priority, platform, etc.)');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Add SLA tracking to conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('sla_policy_id')->nullable()->after('metadata')->constrained()->nullOnDelete();
            $table->timestamp('sla_first_response_due_at')->nullable()->after('sla_policy_id');
            $table->timestamp('sla_resolution_due_at')->nullable()->after('sla_first_response_due_at');
            $table->boolean('sla_first_response_breached')->default(false)->after('sla_resolution_due_at');
            $table->boolean('sla_resolution_breached')->default(false)->after('sla_first_response_breached');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['sla_policy_id']);
            $table->dropColumn([
                'sla_policy_id',
                'sla_first_response_due_at',
                'sla_resolution_due_at',
                'sla_first_response_breached',
                'sla_resolution_breached',
            ]);
        });
        
        Schema::dropIfExists('sla_policies');
    }
};

