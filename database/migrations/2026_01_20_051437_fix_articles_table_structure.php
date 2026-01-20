<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Rename author_id to user_id
            $table->renameColumn('author_id', 'user_id');
            
            // Drop featured_image_id (tidak perlu)
            $table->dropForeign(['featured_image_id']);
            $table->dropColumn('featured_image_id');
            
            // Make excerpt nullable
            $table->text('excerpt')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('user_id', 'author_id');
            $table->foreignId('featured_image_id')->nullable()->constrained('media');
            $table->text('excerpt')->nullable(false)->change();
        });
    }
};
