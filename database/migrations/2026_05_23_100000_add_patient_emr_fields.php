<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (! Schema::hasColumn('patients', 'allergies')) {
                    $table->text('allergies')->nullable()->after('email');
                }
                if (! Schema::hasColumn('patients', 'problem_list')) {
                    $table->text('problem_list')->nullable()->after('allergies');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasColumn('patients', 'problem_list')) {
                    $table->dropColumn('problem_list');
                }
                if (Schema::hasColumn('patients', 'allergies')) {
                    $table->dropColumn('allergies');
                }
            });
        }
    }
};
