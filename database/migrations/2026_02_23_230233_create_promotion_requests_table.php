<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_id')->nullable()->constrained()->onDelete('cascade');

            // Type: offer or banner
            $table->enum('type', ['offer', 'banner']);

            // Common fields
            $table->json('title'); // Translatable
            $table->json('description')->nullable(); // Translatable
            $table->json('images'); // Multiple images

            // Offer specific fields
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->date('offer_starts_at')->nullable();
            $table->date('offer_ends_at')->nullable();

            // Banner specific fields
            $table->string('banner_position')->nullable(); // home_top, home_middle, category_top, etc.
            $table->string('link_url')->nullable();
            $table->date('banner_starts_at')->nullable();
            $table->date('banner_ends_at')->nullable();

            // Status and approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');

            $table->timestamps();
$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
    }
};
