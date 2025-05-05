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
        Schema::create('startups', function (Blueprint $table) {
            $table->id();
            $table->year('founding_year')->nullable();
            $table->string(column: 'country')->nullable();
            $table->string('city')->nullable();
            $table->string('industry')->nullable();
            $table->string('sub_vertical')->nullable();
            $table->string('investment_type')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->decimal('mrr', 15, 2)->nullable();
            $table->string('employees_count')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('image')->nullable(); // ⬅️ added image path
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('startups');
    }
};
