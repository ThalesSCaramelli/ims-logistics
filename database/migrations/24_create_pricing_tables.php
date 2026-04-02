<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old attempts
        foreach ([
            'client_skill_rates', 'client_box_rates',
            'worker_skill_tiers', 'worker_box_tiers',
            'client_skill_additionals', 'client_box_additionals',
            'client_hourly_rates', 'client_container_prices',
            'special_days', 'client_price_history',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        // 1. Container prices — includes box/skill additional config per row
        // Each row = one combination of (client, feet, product)
        // product_id null = Standard container
        Schema::create('client_container_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('feet', ['20', '40']);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Base rates
            $table->decimal('client_rate', 8, 2);
            $table->decimal('worker_rate', 8, 2);

            // Box additionals (optional per row)
            $table->boolean('has_box_additional')->default(false);
            $table->integer('box_threshold')->nullable();
            $table->integer('box_block_size')->nullable();
            $table->decimal('box_client_rate_per_block', 8, 2)->nullable();
            $table->decimal('box_worker_rate_per_block', 8, 2)->nullable();

            // Skill additionals (optional per row)
            $table->boolean('has_skill_additional')->default(false);
            $table->integer('skill_threshold')->nullable();
            $table->integer('skill_block_size')->nullable();
            $table->decimal('skill_client_rate_per_block', 8, 2)->nullable();
            $table->decimal('skill_worker_rate_per_block', 8, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['client_id', 'feet', 'product_id'], 'uq_container_price');
        });

        // 2. Hourly rates (per client + service type)
        Schema::create('client_hourly_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('service_type', ['labour_hire', 'extra_work', 'waiting_time']);
            $table->decimal('client_rate_per_hour', 8, 2);
            $table->decimal('worker_rate_per_hour', 8, 2);
            $table->decimal('holiday_multiplier', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'service_type']);
        });

        // 3. Special days (public holidays — global)
        Schema::create('special_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Price history log
        Schema::create('client_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('section', ['container_prices', 'hourly_rates']);
            $table->json('previous_data');
            $table->json('new_data');
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_price_history');
        Schema::dropIfExists('special_days');
        Schema::dropIfExists('client_hourly_rates');
        Schema::dropIfExists('client_container_prices');
    }
};
