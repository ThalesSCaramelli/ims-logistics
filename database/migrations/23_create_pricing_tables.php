<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables from previous attempts if they exist
        Schema::dropIfExists('client_skill_rates');
        Schema::dropIfExists('client_box_rates');
        Schema::dropIfExists('worker_skill_tiers');
        Schema::dropIfExists('worker_box_tiers');
        Schema::dropIfExists('client_hourly_rates');
        Schema::dropIfExists('client_skill_additionals');
        Schema::dropIfExists('client_box_additionals');
        Schema::dropIfExists('client_container_prices');
        Schema::dropIfExists('special_days');
        Schema::dropIfExists('client_price_history');

        // 1. Container prices (per client + size + product)
        // product_id null = Standard container
        Schema::create('client_container_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('feet', ['20', '40']);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('client_rate', 8, 2);
            $table->decimal('worker_rate', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'feet', 'product_id'], 'uq_container_price');
        });

        // 2. Box additionals (per client + feet)
        // e.g. Frutex 40ft: above 1500 boxes → $50 client / $20 worker per 500 boxes
        //      Frutex 20ft: above 800 boxes  → $30 client / $12 worker per 300 boxes
        Schema::create('client_box_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('feet', ['20', '40']);
            $table->integer('threshold');               // above this qty, start charging
            $table->integer('block_size');              // charge per this many boxes
            $table->decimal('client_rate_per_block', 8, 2);
            $table->decimal('worker_rate_per_block', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'feet'], 'uq_box_additional');
        });

        // 3. Skill additionals (per client + feet)
        Schema::create('client_skill_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('feet', ['20', '40']);
            $table->integer('threshold');
            $table->integer('block_size');
            $table->decimal('client_rate_per_block', 8, 2);
            $table->decimal('worker_rate_per_block', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'feet'], 'uq_skill_additional');
        });

        // 4. Hourly rates (per client + service type)
        // holiday_multiplier applied on special days (e.g. 1.5 = 50% surcharge)
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

        // 5. Special days (public holidays — global)
        Schema::create('special_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Price history log
        // Every time a price is saved, the OLD values are stored here
        Schema::create('client_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            // Which section was changed
            $table->enum('section', [
                'container_prices',
                'box_additionals',
                'skill_additionals',
                'hourly_rates',
            ]);
            $table->enum('feet', ['20', '40'])->nullable(); // null for hourly_rates
            $table->json('previous_data');  // full snapshot of old values
            $table->json('new_data');       // full snapshot of new values
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_price_history');
        Schema::dropIfExists('special_days');
        Schema::dropIfExists('client_hourly_rates');
        Schema::dropIfExists('client_skill_additionals');
        Schema::dropIfExists('client_box_additionals');
        Schema::dropIfExists('client_container_prices');
    }
};
