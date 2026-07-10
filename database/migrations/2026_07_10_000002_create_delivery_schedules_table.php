<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable(); // will be linked or updated
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->string('delivery_window')->nullable(); // e.g. "10:00 AM - 12:00 PM"
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
