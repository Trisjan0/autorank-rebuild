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
            $table->string('rank_category');
            $table->decimal('kra1_weight', 5, 4);
            $table->decimal('kra2_weight', 5, 4);
            $table->decimal('kra3_weight', 5, 4);
            $table->decimal('kra4_weight', 5, 4);
            $table->boolean('is_active')->default(false);
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
