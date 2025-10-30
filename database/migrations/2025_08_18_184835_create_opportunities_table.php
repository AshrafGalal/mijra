<?php

use App\Models\Tenant\Customer;
use App\Models\Tenant\Stage;
use App\Models\Tenant\User;
use App\Models\Tenant\Workflow;
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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Workflow::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Stage::class)->nullable()->constrained()->nullOnDelete();
            $table->string('priority')->default(\App\Enum\PriorityEnum::MEDIUM->value);
            $table->tinyInteger('status')->default(\App\Enum\OpportunityStatusEnum::ACTIVE->value);
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
