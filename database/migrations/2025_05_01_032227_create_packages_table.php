<?php

use App\Models\Package;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->enum('duration', ['per_month', 'annually']);
            $table->enum('features', ['2', '5', '10', '12']);
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

};
