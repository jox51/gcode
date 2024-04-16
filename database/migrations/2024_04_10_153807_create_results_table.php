<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->string('sport');
            $table->json('a_results');
            $table->json('b_results');
            $table->json('c_results');
            $table->json('d_results');
            $table->json('e_results');
            $table->json('f_results');
            $table->json('g_results');
            $table->json('h_results');
            $table->json('i_results');
            $table->json('total_results');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('results');
    }
};
