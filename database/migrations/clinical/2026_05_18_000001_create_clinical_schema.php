<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AHOP clinical schema on PostgreSQL (dual-database mode).
 *
 * References to users, companies, and assets are stored as integer IDs only —
 * no foreign keys across MySQL ↔ PostgreSQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('patient_number', 20)->unique();
            $table->string('full_name');
            $table->string('sex', 1);
            $table->date('birthdate');
            $table->string('contact_number', 30)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('full_name');
            $table->index('birthdate');
            $table->index('company_id');
        });

        Schema::create('opd_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('visit_number', 20)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('physician_id')->nullable();
            $table->dateTime('visit_date');
            $table->string('visit_type', 20)->default('initial');
            $table->string('status', 20)->default('scheduled');
            $table->text('chief_complaint')->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->unsignedSmallInteger('pulse_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->text('assessment')->nullable();
            $table->text('diagnosis')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients');
            $table->index(['patient_id', 'visit_date']);
            $table->index('status');
            $table->index('physician_id');
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_number', 20)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('opd_visit_id')->nullable();
            $table->unsignedInteger('ordered_by')->nullable();
            $table->string('test_panel', 100);
            $table->string('status', 20)->default('ordered');
            $table->string('priority', 20)->default('routine');
            $table->text('clinical_notes')->nullable();
            $table->dateTime('ordered_at');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('opd_visit_id')->references('id')->on('opd_visits');
            $table->index(['patient_id', 'ordered_at']);
            $table->index('status');
            $table->index('ordered_by');
        });

        Schema::create('lab_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lab_order_id');
            $table->string('test_code', 50)->nullable();
            $table->string('test_name', 150);
            $table->string('result_value', 100);
            $table->string('unit', 30)->nullable();
            $table->string('reference_range', 100)->nullable();
            $table->string('flag', 20)->nullable();
            $table->dateTime('result_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('lab_order_id')->references('id')->on('lab_orders')->onDelete('cascade');
            $table->index('lab_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('opd_visits');
        Schema::dropIfExists('patients');
    }
};
