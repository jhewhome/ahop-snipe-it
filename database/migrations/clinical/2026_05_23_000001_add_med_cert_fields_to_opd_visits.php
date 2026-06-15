<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opd_visits', function (Blueprint $table) {
            if (! Schema::hasColumn('opd_visits', 'rest_days')) {
                $table->unsignedSmallInteger('rest_days')->nullable()->after('diagnosis');
            }
            if (! Schema::hasColumn('opd_visits', 'med_cert_remarks')) {
                $table->text('med_cert_remarks')->nullable()->after('rest_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opd_visits', function (Blueprint $table) {
            if (Schema::hasColumn('opd_visits', 'med_cert_remarks')) {
                $table->dropColumn('med_cert_remarks');
            }
            if (Schema::hasColumn('opd_visits', 'rest_days')) {
                $table->dropColumn('rest_days');
            }
        });
    }
};
