<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physiotherapists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('clinic_name')->nullable();
            $table->string('city')->nullable();
            $table->string('specialty')->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_url')->nullable();

            $table->text('bio_short')->nullable();
            $table->boolean('is_accepting_consultations')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physiotherapists');
    }
};
