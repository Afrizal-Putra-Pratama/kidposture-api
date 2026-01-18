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
    Schema::create('screenings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('child_id')->constrained('children')->onDelete('cascade');
        $table->unsignedBigInteger('user_id'); // parent yg submit
        $table->float('score')->nullable();    // skor postur 0-100
        $table->string('category')->nullable(); // GOOD / FAIR / ATTENTION
        $table->json('metrics')->nullable();   // simpan raw AI metrics
        $table->text('summary')->nullable();   // ringkasan hasil ke bahasa awam
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
