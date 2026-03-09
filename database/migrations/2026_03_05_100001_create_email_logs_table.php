<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->json('to'); // array of email addresses
            $table->string('subject', 500);
            $table->string('mailable_class', 255)->nullable();
            $table->string('status', 20)->default('sent'); // sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable(); // for failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
