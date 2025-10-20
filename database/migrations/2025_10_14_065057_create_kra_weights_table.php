<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kra_weights', function (Blueprint $table) {
            $table->id();
            $table->string('rank_category')->unique();
            $table->unsignedInteger('kra1_weight');
            $table->unsignedInteger('kra2_weight');
            $table->unsignedInteger('kra3_weight');
            $table->unsignedInteger('kra4_weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kra_weights');
    }
};
