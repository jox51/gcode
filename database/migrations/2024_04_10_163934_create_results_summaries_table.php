<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('results_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('sport');
            $table->date('date')->comment('The date these results apply to');
            $table->json('overall')->comment('Overall correctness statistics');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('results_summary');
    }
};
