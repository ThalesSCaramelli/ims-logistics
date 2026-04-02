<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('worker_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->string('visa_class', 20);
            $table->date('valid_until');
            $table->boolean('work_permitted')->default(true);
            $table->integer('fortnightly_hours_limit')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('worker_visas'); }
};
