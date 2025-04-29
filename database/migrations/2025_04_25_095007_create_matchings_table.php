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
        Schema::create('matchings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('startup_id'); // references users table
            $table->unsignedBigInteger('investor_id'); // references users table
            $table->enum('status', ['matched', 'rejected'])->default('matched');
            $table->timestamps();

            $table->foreign('startup_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('investor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchings');
    }
};
