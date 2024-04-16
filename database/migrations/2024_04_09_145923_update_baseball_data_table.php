<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('baseball_data', function (Blueprint $table) {
            $table->string('algo_rank')->default('J');
            $table->string('to_win')->nullable();
            $table->boolean('auto_over')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('baseball_data', function (Blueprint $table) {
            $table->dropColumn('algo_rank');
            $table->dropColumn('to_win');
            $table->dropColumn('auto_over');
        });
    }
};
