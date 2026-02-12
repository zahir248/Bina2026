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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('event_category_id')->constrained('event_categories')->onDelete('cascade');
            $table->string('location');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->integer('ticket_stock')->nullable();
            $table->text('images')->nullable(); // JSON array of image paths
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
