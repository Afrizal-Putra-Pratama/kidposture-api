<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('screening_images', function (Blueprint $table) {
        $table->string('processed_path')->nullable()->after('path');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screening_images', function (Blueprint $table) {
            //
        });
    }
};
