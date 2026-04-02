<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_demands', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Container quantities
            $table->integer('qty_20ft')->default(0);
            $table->integer('qty_40ft')->default(0);

            // For hour/driver services (Labor Hire, Driver)
            $table->integer('qty_workers')->default(0); // e.g. "1 driver"

            // Status tracking
            // pending    = received, no book created yet
            // partial    = at least 1 book created, still missing crews
            // allocated  = all containers have crews allocated
            // cancelled  = cancelled by client
            $table->enum('status', ['pending', 'partial', 'allocated', 'cancelled'])
                  ->default('pending');

            // How many crews (books) have been allocated so far
            $table->integer('crews_allocated')->default(0);

            $table->text('notes')->nullable(); // free text from client message
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_demands');
    }
};
