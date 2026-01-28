<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physiotherapists', function (Blueprint $table) {
            if (!Schema::hasColumn('physiotherapists', 'bio')) {
                $table->text('bio')->nullable()->after('specialty');
            }
            if (!Schema::hasColumn('physiotherapists', 'photo')) {
                $table->string('photo')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('physiotherapists', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('physiotherapists', 'address')) {
                $table->string('address')->nullable()->after('city');
            }
            if (!Schema::hasColumn('physiotherapists', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (!Schema::hasColumn('physiotherapists', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('physiotherapists', 'practice_hours')) {
                $table->text('practice_hours')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('physiotherapists', 'consultation_fee')) {
                $table->integer('consultation_fee')->nullable()->after('practice_hours');
            }
            if (!Schema::hasColumn('physiotherapists', 'status')) {
                $table->string('status')->default('pending')->after('consultation_fee');
            }
            if (!Schema::hasColumn('physiotherapists', 'certificate_path')) {
                $table->string('certificate_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('physiotherapists', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('certificate_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('physiotherapists', function (Blueprint $table) {
            $columns = ['bio', 'photo', 'phone', 'address', 'latitude', 'longitude', 'practice_hours', 'consultation_fee', 'status', 'certificate_path', 'verified_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('physiotherapists', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
