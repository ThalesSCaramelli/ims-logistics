<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('client_price_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_price_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('client_value', 10, 2)->default(0);
            $table->decimal('labor_value', 10, 2)->default(0);
            $table->string('unit')->default('per_container');
            $table->enum('rule', ['required', 'optional'])->default('optional');
            $table->string('condition')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('client_price_extras'); }
};
