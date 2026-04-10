<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Additionals configured per client
        Schema::create('client_container_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');                   // e.g. "Pallet Removal", "Extra Weight"
            $table->enum('feet', ['20', '40', 'both'])->default('both');
            $table->decimal('client_rate', 8, 2)->default(0);
            $table->decimal('worker_rate', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Which additionals are marked on each container in a worksheet
        Schema::create('job_container_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('job_containers')->cascadeOnDelete();
            $table->foreignId('additional_id')->constrained('client_container_additionals')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['container_id', 'additional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_container_additionals');
        Schema::dropIfExists('client_container_additionals');
    }
};
