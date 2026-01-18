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
    Schema::create('children', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->date('birth_date')->nullable();
        $table->enum('gender', ['M', 'F'])->nullable(); // M=male, F=female
        $table->float('weight')->nullable(); // kg
        $table->float('height')->nullable(); // cm
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('children');
}

};
