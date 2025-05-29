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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('original_price', 8, 2);
            $table->decimal('discounted_price', 8, 2);
            $table->string('currency', 3)->default('BRL');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_urgency')->default(false);
            $table->boolean('show_floating_banner')->default(false);
            $table->datetime('valid_from')->nullable();
            $table->datetime('valid_until')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
