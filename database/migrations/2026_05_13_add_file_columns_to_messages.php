<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum ditambah
            if (!Schema::hasColumn('messages', 'file_url')) {
                $table->string('file_url', 1000)->nullable()->after('body');
            }
            if (!Schema::hasColumn('messages', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_url');
            }
            // Update enum type untuk include image, video, file
            // Laravel tidak bisa alter enum langsung, pakai string instead
            if (Schema::hasColumn('messages', 'type')) {
                $table->string('type')->default('text')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumnIfExists('file_url');
            $table->dropColumnIfExists('file_name');
        });
    }
};