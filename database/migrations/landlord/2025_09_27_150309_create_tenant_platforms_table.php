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
        Schema::create('tenant_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Landlord\Platform::class)->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable()->unique()->comment('e.g. shop domain, facebook page id');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->string('expires_at')->nullable();
            $table->json('meta')->nullable();
            $table->string('status')->nullable();
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
