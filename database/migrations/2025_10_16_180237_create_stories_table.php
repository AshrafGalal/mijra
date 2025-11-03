<?php

use App\Models\Tenant\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//        Schema::dropIfExists('stories');

        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->index();
            $table->string('external_identifier_id')->index();
            $table->text('body')->nullable();
            $table->boolean('has_media')->default(false);
            $table->string('type')->default(\App\Enum\MessageTypeEnum::TEXT->value); // text,image,video
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // story specific meta
            $table->string('contact_identifier_id')->comment('who posted story')->index(); // who posted (contact id)
            $table->string('contact_name')->comment('name of owner for  story')->nullable(); // who posted (contact id)
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->nullOnDelete();
            $table->unique(['platform', 'external_identifier_id']);
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
