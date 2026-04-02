<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('job_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('created_by')->constrained('workers');
            $table->timestamps();
        });
        Schema::create('job_team_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->unique(['job_team_id', 'worker_id']);
        });
        Schema::create('job_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->string('container_number', 50);
            $table->enum('feet', ['20', '40']);
            $table->foreignId('product_id')->constrained();
            $table->integer('boxes_count')->nullable();
            $table->integer('skills_count')->nullable();
            $table->string('description_extra')->nullable();
            $table->json('photos')->nullable();
            $table->decimal('override_client_amount', 10, 2)->nullable();
            $table->string('override_reason')->nullable();
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('override_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('job_container_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_container_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_team_id')->constrained()->cascadeOnDelete();
            $table->integer('feet_completed');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('worksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('filled_by')->nullable()->constrained('workers')->nullOnDelete();
            $table->json('filled_data')->nullable();
            $table->timestamp('filled_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('workers')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('client_signature_type', ['digital', 'waived'])->nullable();
            $table->text('client_signature_data')->nullable();
            $table->string('client_signed_by')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('waived_reason')->nullable();
            $table->timestamp('waived_at')->nullable();
            $table->json('extra_work')->nullable();
            $table->json('waiting_time')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('corrections')->nullable();
            $table->enum('sync_status', ['draft', 'pending', 'synced', 'approved', 'paid'])->default('draft');
            $table->timestamp('synced_at')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
        Schema::create('worker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worksheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_container_team_id')->nullable()->constrained('job_container_teams')->nullOnDelete();
            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('extras_amount', 10, 2)->default(0);
            $table->decimal('topup_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->date('week_period');
            $table->enum('status', ['calculated', 'approved', 'paid'])->default('calculated');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('worker_payments');
        Schema::dropIfExists('worksheets');
        Schema::dropIfExists('job_container_teams');
        Schema::dropIfExists('job_containers');
        Schema::dropIfExists('job_team_workers');
        Schema::dropIfExists('job_teams');
    }
};
