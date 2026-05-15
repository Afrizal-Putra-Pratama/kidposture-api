<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('physio_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('screening_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['parent_id', 'physio_id', 'screening_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};