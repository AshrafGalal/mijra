<?php

use App\Enum\DiscountTypeEnum;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_type')->default(DiscountTypeEnum::PERCENTAGE->value);
            $table->decimal('vat_percentage', 10, 2)->default(0);
            $table->tinyInteger('status')->default(\App\Enum\ProductStatusEnum::PUBLISHED->value);
            $table->foreignIdFor(\App\Models\Tenant\Category::class)->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
