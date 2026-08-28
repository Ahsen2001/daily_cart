<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rider_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('delivery_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('rider_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->json('tags')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('visible')->index();
            $table->text('report_reason')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'status']);
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_ratings');
    }
};
