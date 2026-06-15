<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patients') && ! Schema::hasColumn('patients', 'email')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->string('email', 150)->nullable()->after('contact_number');
            });
        }

        if (Schema::hasTable('appointments') && ! Schema::hasColumn('appointments', 'reminder_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dateTime('reminder_sent_at')->nullable()->after('notes');
                $table->index('reminder_sent_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients') && Schema::hasColumn('patients', 'email')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'reminder_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex(['reminder_sent_at']);
                $table->dropColumn('reminder_sent_at');
            });
        }
    }
};
