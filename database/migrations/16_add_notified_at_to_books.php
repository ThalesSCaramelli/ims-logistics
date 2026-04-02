<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('notes');
            $table->foreignId('notified_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['notified_by']);
            $table->dropColumn(['notified_at', 'notified_by']);
        });
    }
};
