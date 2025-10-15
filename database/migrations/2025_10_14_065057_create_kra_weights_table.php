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
            $table->foreignId('faculty_rank_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('instruction_weight');
            $table->unsignedInteger('research_weight');
            $table->unsignedInteger('extension_weight');
            $table->unsignedInteger('professional_development_weight');
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
