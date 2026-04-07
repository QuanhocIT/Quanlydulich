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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->date('departure_date');
            $table->integer('num_adults')->default(1);
            $table->integer('num_children')->default(0);
            $table->decimal('total_price', 15, 2);
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded
            $table->string('payment_method')->nullable();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('special_requests')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
