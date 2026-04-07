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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('itinerary')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('price_sale', 15, 2)->nullable();
            $table->integer('duration_days');
            $table->integer('max_guests')->default(20);
            $table->string('difficulty')->default('easy'); // easy, moderate, hard
            $table->string('tour_type')->default('domestic'); // domestic, international
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
