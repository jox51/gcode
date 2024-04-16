<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('results_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('results_summary_id')->constrained()->onDelete('cascade');
            $table->char('algo_rank', 1)->comment('Algo rank label');
            $table->integer('correct');
            $table->integer('total');
            $table->double('percentage', 5, 2)->comment('Calculated percentage of correct predictions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('results_details');
    }
};
