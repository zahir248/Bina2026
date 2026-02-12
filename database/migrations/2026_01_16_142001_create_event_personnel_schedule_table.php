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
        Schema::create('event_personnel_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_personnel_id')->constrained('event_personnel')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['event_personnel_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_personnel_schedule');
    }
};
