<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ComplaintStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('message');

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type')->default('order');

            $table->enum(
                'status',
                array_column(ComplaintStatus::cases(), 'value')
            )->default(ComplaintStatus::NEW->value);

            $table->text('admin_response')->nullable();

            $table->json('images')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
