<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            // refer ke physiotherapists table
            $table->foreignId('physiotherapist_id')
                ->nullable()
                ->constrained('physiotherapists')
                ->nullOnDelete();

            $table->string('referral_status')
                ->default('none'); // none/requested/accepted/completed
        });
    }

    public function down(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->dropForeign(['physiotherapist_id']);
            $table->dropColumn(['physiotherapist_id', 'referral_status']);
        });
    }
};
