<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('hockey_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->datetime('date')->nullable();
            $table->string('start_date');
            $table->json('teams');
            $table->string('algo_rank')->default('J');
            $table->string('to_win')->nullable();
            $table->boolean('auto_over')->default(false);
            $table->json('odds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('hockey_data');
    }
};
