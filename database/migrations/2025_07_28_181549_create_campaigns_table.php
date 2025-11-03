<?php

use App\Enum\CampaignStatusEnum;
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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('type')->comment('refer to is this Campaign for retention , follow-up or whatever')->nullable();
            $table->string('channel');
            $table->string('title');
            $table->string('content');
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('status')->default(CampaignStatusEnum::ACTIVE->value);
            $table->tinyInteger('target')->comment('define if campaign is for specific segment,specific customers of for all')->default(\App\Enum\CampaignTargetEnum::ALL_CUSTOMERS->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
