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
        Schema::create('whatsapp_phones', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('phone_number')->nullable();
            $table->string('phone_label')->nullable(); // Friendly name like "Sales", "Support"
            $table->tinyInteger('status')->default(\App\Enum\WhatsappPhoneStatusEnum::INITIALIZING->value);
            $table->text('qr_code')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Ensure unique phone per tenant
            $table->unique(['tenant_id', 'phone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_phones');
    }
};
