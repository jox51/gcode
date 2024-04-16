<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('subscription_status')->default(false);
            $table->string('agreement_id')->nullable();
            $table->string('payer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription_status');
            $table->dropColumn('agreement_id');
            $table->dropColumn('payer_id');
        });
    }
};
