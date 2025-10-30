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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->comment('reference to is for promotional,retention,....')->nullable();
            $table->string('template_type')->comment('sms,whatsapp,email'); // email, sms, whatsapp, push, etc.
            $table->longText('content');
            $table->string('sender_number')->nullable();
            $table->text('header_content')->nullable();
            $table->text('footer_content')->nullable();
            $table->tinyInteger('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
