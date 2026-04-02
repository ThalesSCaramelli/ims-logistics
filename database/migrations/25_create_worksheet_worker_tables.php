<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workers per container with optional partial split
        // qty = fraction of the container this group worked
        // e.g. Part 1: qty=0.6 workers=[Thales,Pedro], Part 2: qty=0.4 workers=[João,André]
        // For simple mode (no split): one row per worker, qty=1.0, part=1
        Schema::create('container_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')
                  ->constrained('job_containers')
                  ->cascadeOnDelete();
            $table->foreignId('worker_id')
                  ->constrained('workers')
                  ->cascadeOnDelete();
            $table->integer('part')->default(1);
            // qty = fraction of container (0.1 to 1.0)
            // in simple mode all workers in part 1 have qty=1.0 total
            $table->decimal('qty', 4, 2)->default(1.00);
            $table->timestamps();

            // one record per worker per part per container
            $table->unique(['container_id', 'worker_id', 'part'], 'uq_container_worker_part');
        });

        // Multiple extra service lines per worksheet
        // Replaces the single extra_work / waiting_time columns
        Schema::create('worksheet_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')
                  ->constrained('worksheets')
                  ->cascadeOnDelete();
            $table->enum('service_type', ['extra_work', 'waiting_time', 'labour_hire']);
            $table->decimal('hours', 6, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worksheet_services');
        Schema::dropIfExists('container_workers');
    }
};
