<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('client_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('client_base_price', 10, 2);
            $table->decimal('client_secondary_price', 10, 2)->nullable();
            $table->integer('client_boxes_limit')->nullable();
            $table->integer('client_boxes_block_size')->nullable();
            $table->decimal('client_boxes_block_price', 10, 2)->nullable();
            $table->integer('client_skills_limit')->nullable();
            $table->integer('client_skills_block_size')->nullable();
            $table->decimal('client_skills_block_price', 10, 2)->nullable();
            $table->decimal('labor_base_price', 10, 2);
            $table->decimal('labor_secondary_price', 10, 2)->nullable();
            $table->integer('labor_boxes_limit')->nullable();
            $table->integer('labor_boxes_block_size')->nullable();
            $table->decimal('labor_boxes_block_price', 10, 2)->nullable();
            $table->integer('labor_skills_limit')->nullable();
            $table->integer('labor_skills_block_size')->nullable();
            $table->decimal('labor_skills_block_price', 10, 2)->nullable();
            $table->json('global_additionals')->nullable();
            $table->decimal('extra_work_client_rate', 10, 2)->nullable();
            $table->decimal('extra_work_labor_rate', 10, 2)->nullable();
            $table->decimal('waiting_time_client_rate', 10, 2)->nullable();
            $table->decimal('waiting_time_labor_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'site_id', 'product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('client_prices'); }
};
