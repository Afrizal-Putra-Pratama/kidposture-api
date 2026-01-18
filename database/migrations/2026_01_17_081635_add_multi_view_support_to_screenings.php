<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('screenings', function (Blueprint $table) {
            // Tambah kolom untuk tracking multi-view
            $table->boolean('is_multi_view')->default(false)->after('summary');
            $table->integer('total_views')->default(1)->after('is_multi_view'); // 1, 2, atau 3
        });
    }

    public function down()
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->dropColumn(['is_multi_view', 'total_views']);
        });
    }
};
