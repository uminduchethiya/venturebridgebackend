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
        Schema::create('startup_to_match_investor_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id');
            $table->string('pitch_deck')->nullable();
            $table->string('other_document')->nullable();
            $table->longText('signature')->nullable(); // base64 string or file path

            // Status for approval/rejection of full submission
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_comment')->nullable();

            $table->timestamps();

            $table->foreign('match_id')
                  ->references('id')
                  ->on('startup_to_match_investor')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('startup_to_match_investor_documents');
    }
};
