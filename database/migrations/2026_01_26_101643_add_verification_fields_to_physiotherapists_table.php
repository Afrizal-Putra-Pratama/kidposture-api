<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physiotherapists', function (Blueprint $table) {
            $table->string('certificate_path')->nullable()->after('photo_url');
            $table->boolean('is_verified')->default(false)->after('certificate_path');
            $table->boolean('is_active')->default(true)->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('physiotherapists', function (Blueprint $table) {
            $table->dropColumn(['certificate_path', 'is_verified', 'is_active']);
        });
    }
};
