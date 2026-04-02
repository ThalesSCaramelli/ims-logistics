<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('abn', 20)->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->date('return_date')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->boolean('has_forklift')->default(false);
            $table->string('forklift_licence_number')->nullable();
            $table->date('forklift_expiry')->nullable();
            $table->string('forklift_state', 10)->nullable();
            $table->boolean('is_australian')->default(true);
            $table->decimal('min_weekly', 10, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('workers'); }
};
