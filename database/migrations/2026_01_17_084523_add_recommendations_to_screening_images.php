<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('screening_images', function (Blueprint $table) {
            $table->json('recommendations')->nullable()->after('processed_path');
        });
    }

    public function down()
    {
        Schema::table('screening_images', function (Blueprint $table) {
            $table->dropColumn('recommendations');
        });
    }
};
