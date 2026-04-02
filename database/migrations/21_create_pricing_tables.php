<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── client_prices (já corrigido) ──────────────
        Schema::table('client_prices', function (Blueprint $table) {
            if (Schema::hasColumn('client_prices', 'rate')) {
                $table->renameColumn('rate', 'client_rate');
            }
            if (!Schema::hasColumn('client_prices', 'worker_rate')) {
                $table->decimal('worker_rate', 8, 2)->default(0)->after('client_rate');
            }
            if (!Schema::hasColumn('client_prices', 'feet')) {
                $table->enum('feet', ['20', '40'])->nullable()->after('product_id');
            }
        });

        // ── Skill rates per client ────────────────────
        if (!Schema::hasTable('client_skill_rates')) {
            Schema::create('client_skill_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->decimal('client_rate_per_skill', 8, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique('client_id');
            });
        }

        if (!Schema::hasTable('worker_skill_tiers')) {
            Schema::create('worker_skill_tiers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->integer('from_qty');
                $table->integer('to_qty')->nullable();
                $table->decimal('worker_bonus', 8, 2);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Box rates per client ──────────────────────
        if (!Schema::hasTable('client_box_rates')) {
            Schema::create('client_box_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->decimal('client_rate_per_box', 8, 4)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique('client_id');
            });
        }

        if (!Schema::hasTable('worker_box_tiers')) {
            Schema::create('worker_box_tiers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->integer('from_qty');
                $table->integer('to_qty')->nullable();
                $table->decimal('worker_bonus', 8, 2);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── Hourly service rates per client ───────────
        if (!Schema::hasTable('client_hourly_rates')) {
            Schema::create('client_hourly_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->enum('service_type', [
                    'extra_work', 'waiting_time', 'labour_hire', 'driver', 'other',
                ]);
                $table->string('label');
                $table->decimal('client_rate_per_hour', 8, 2);
                $table->decimal('worker_rate_per_hour', 8, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['client_id', 'service_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_hourly_rates');
        Schema::dropIfExists('worker_box_tiers');
        Schema::dropIfExists('client_box_rates');
        Schema::dropIfExists('worker_skill_tiers');
        Schema::dropIfExists('client_skill_rates');

        Schema::table('client_prices', function (Blueprint $table) {
            if (Schema::hasColumn('client_prices', 'client_rate')) {
                $table->renameColumn('client_rate', 'rate');
            }
            $table->dropColumn(['worker_rate', 'feet']);
        });
    }
};
