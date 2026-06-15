<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Billing tables for AHOP when clinical migrations run on the default MySQL connection.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billing_invoices')) {
            return;
        }

        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::create('billable_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 50)->default('general');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice_number', 20)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('opd_visit_id')->nullable();
            $table->unsignedInteger('appointment_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->dateTime('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients');
            if (Schema::hasTable('opd_visits')) {
                $table->foreign('opd_visit_id')->references('id')->on('opd_visits');
            }
            $table->index(['patient_id', 'issued_at']);
            $table->index('status');
        });

        Schema::create('billing_line_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billing_invoice_id');
            $table->unsignedInteger('billable_service_id')->nullable();
            $table->string('description');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_amount', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->foreign('billing_invoice_id')->references('id')->on('billing_invoices')->onDelete('cascade');
            $table->foreign('billable_service_id')->references('id')->on('billable_services');
        });

        Schema::create('billing_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billing_invoice_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('reference', 100)->nullable();
            $table->dateTime('paid_at');
            $table->unsignedInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('billing_invoice_id')->references('id')->on('billing_invoices')->onDelete('cascade');
            $table->index('paid_at');
        });

        $this->seedBillableServices();
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_payments');
        Schema::dropIfExists('billing_line_items');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('billable_services');
    }

    private function seedBillableServices(): void
    {
        $now = now();
        $services = [
            ['code' => 'CONSULT', 'name' => 'General Consultation', 'category' => 'consultation', 'default_amount' => 500],
            ['code' => 'FOLLOWUP', 'name' => 'Follow-up Visit', 'category' => 'consultation', 'default_amount' => 350],
            ['code' => 'EMERGENCY', 'name' => 'Emergency Consultation', 'category' => 'consultation', 'default_amount' => 800],
            ['code' => 'CBC', 'name' => 'Complete Blood Count (CBC)', 'category' => 'laboratory', 'default_amount' => 250],
            ['code' => 'URINALYSIS', 'name' => 'Urinalysis', 'category' => 'laboratory', 'default_amount' => 150],
            ['code' => 'XRAY', 'name' => 'X-Ray (per view)', 'category' => 'imaging', 'default_amount' => 600],
            ['code' => 'ECG', 'name' => 'ECG', 'category' => 'procedure', 'default_amount' => 400],
            ['code' => 'MISC', 'name' => 'Miscellaneous / Other', 'category' => 'general', 'default_amount' => 0],
        ];

        foreach ($services as $service) {
            DB::table('billable_services')->insert(array_merge($service, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
};
