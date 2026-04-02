<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Link book back to the demand it was created from
            // Nullable — books can be created without a demand
            $table->foreignId('demand_id')
                  ->nullable()
                  ->after('created_by')
                  ->constrained('day_demands')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['demand_id']);
            $table->dropColumn('demand_id');
        });
    }
};
