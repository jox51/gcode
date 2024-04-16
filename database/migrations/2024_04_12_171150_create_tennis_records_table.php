<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tennis_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id');
            $table->timestamp('date'); // Storing the date and time of the fixture
            $table->unsignedBigInteger('player1_id');
            $table->unsignedBigInteger('player2_id');
            $table->unsignedBigInteger('tournament_id');
            $table->json('player1'); // Storing player data as JSON
            $table->json('player2');
            $table->json('player1_record')->nullable(); // Storing record data as JSON
            $table->json('player2_record')->nullable();
            $table->string('type'); // ATP or WTA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tennis_records');
    }
};
