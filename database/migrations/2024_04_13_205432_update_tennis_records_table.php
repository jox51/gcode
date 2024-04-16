<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('tennis_records', function (Blueprint $table) {
            $table->string('algo_rank')->default('J');
            $table->string('to_win')->nullable();
            $table->boolean('auto_over')->default(false);
            $table->json('event_data')->nullable();
            $table->json('homeTeam_records_lp')->nullable();
            $table->json('awayTeam_records_lp')->nullable();
            $table->json('ranking_parameters')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('tennis_records', function (Blueprint $table) {
            $table->dropColumn('algo_rank');
            $table->dropColumn('to_win');
            $table->dropColumn('auto_over');
            $table->dropColumn('event_data');
            $table->dropColumn('homeTeam_records_lp');
            $table->dropColumn('awayTeam_records_lp');
            $table->dropColumn('ranking_parameters');
        });
    }
};
