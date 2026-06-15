<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Appointments table for AHOP when clinical migrations run on the default MySQL connection.
 * Skip if using PostgreSQL-only clinical path (table already created there).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('appointments')) {
            return;
        }

        if (! Schema::hasTable('patients') || ! Schema::hasTable('opd_visits')) {
            return;
        }

        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('appointment_number', 20)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('physician_id')->nullable();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('visit_type', 20)->default('initial');
            $table->string('status', 20)->default('scheduled');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('opd_visit_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('opd_visit_id')->references('id')->on('opd_visits');
            $table->index(['scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
