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
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('founding_round', 255);
            $table->decimal('investment_amount', 15, 2);
            $table->decimal('valuation', 15, 2);
            $table->integer('number_of_investors');
            $table->integer('founding_year');
            $table->decimal('growth_rate', 8, 2);
            $table->string('business_type', 100);
            $table->string('product_type', 100);
            $table->string('company_usage', 100);
            $table->decimal('annual_revenue', 15, 2);
            $table->decimal('mrr', 15, 2);
            $table->string('employees_count', 100);
            $table->decimal('price', 15, 2);
            $table->string('linkedin_url');
            $table->string('facebook_url');
            $table->string('twitter_url');
            $table->string('image')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investors');
    }
};
