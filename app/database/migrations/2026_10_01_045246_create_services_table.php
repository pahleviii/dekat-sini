<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('whatsapp', 20);
            $table->string('phone', 20)->nullable();
            $table->string('address');
            $table->string('city', 100);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('price_from')->default(0);
            $table->enum('status', ['active', 'pending', 'paused'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->index(['status', 'is_verified']);
            $table->index(['city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
