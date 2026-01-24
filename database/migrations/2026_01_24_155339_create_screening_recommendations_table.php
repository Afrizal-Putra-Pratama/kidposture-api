<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained('screenings')->onDelete('cascade');
            $table->foreignId('physio_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['exercise', 'education', 'note', 'referral'])->default('note');
            $table->string('title');
            $table->text('content');
            $table->string('media_url')->nullable(); // untuk video/gambar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_recommendations');
    }
};
