<?php

use App\Models\Tenant\Template;
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
        Schema::create('template_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Template::class)->constrained()->cascadeOnDelete();
            $table->string('button_text');
            $table->enum('button_type', ['url', 'phone', 'whatsapp', 'email'])->default('url');
            $table->text('action_value'); // URL, phone, WhatsApp number, email
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_buttons');
    }
};
