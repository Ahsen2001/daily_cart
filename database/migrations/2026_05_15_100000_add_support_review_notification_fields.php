<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'assigned_admin_id')) {
                $table->foreignId('assigned_admin_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('support_tickets', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('assigned_admin_id')->index();
            }
        });

        if (! Schema::hasTable('support_ticket_replies')) {
            Schema::create('support_ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->timestamps();

                $table->index(['support_ticket_id', 'created_at']);
            });
        }

        DB::table('reviews')->whereIn('status', ['pending', 'approved', 'rejected'])->update(['status' => 'visible']);
        DB::statement("ALTER TABLE reviews MODIFY status ENUM('visible', 'hidden', 'reported') NOT NULL DEFAULT 'visible'");

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'image')) {
                $table->string('image')->nullable()->after('comment');
            }

            $table->unique(['customer_id', 'product_id', 'order_id'], 'reviews_customer_product_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_customer_product_order_unique');

            if (Schema::hasColumn('reviews', 'image')) {
                $table->dropColumn('image');
            }
        });

        DB::table('reviews')->whereIn('status', ['visible', 'hidden', 'reported'])->update(['status' => 'approved']);
        DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");

        Schema::dropIfExists('support_ticket_replies');

        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
            if (Schema::hasColumn('support_tickets', 'assigned_admin_id')) {
                $table->dropConstrainedForeignId('assigned_admin_id');
            }
        });
    }
};
