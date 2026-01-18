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
    Schema::create('screening_images', function (Blueprint $table) {
        $table->id();
        $table->foreignId('screening_id')->constrained('screenings')->onDelete('cascade');
        $table->string('type'); // FRONT / SIDE / BACK
        $table->string('path'); // path file di storage
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_images');
    }
};
