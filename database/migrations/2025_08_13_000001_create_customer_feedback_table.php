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
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->foreignId('feedback_category_id')->constrained('feedback_categories')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('detailed_review');
            $table->enum('source', \App\Enum\FeedbackSourceEnum::values())
                ->default(\App\Enum\FeedbackSourceEnum::WEBSITE->value);
            $table->string('status')->default(\App\Enum\CustomerFeedbackStatusEnum::NEW->value)->comment('values from Feedback enum class '.implode(',', \App\Enum\CustomerFeedbackStatusEnum::values()));

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedback');
    }
};
