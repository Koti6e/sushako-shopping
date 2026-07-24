<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->unique();
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('email');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('business_type')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('India');
            $table->string('logo')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('onboarding_status')->default('submitted')->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'onboarding_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
