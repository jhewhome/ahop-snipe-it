<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bhc_id', 20)->unique();
            $table->string('full_name');
            $table->enum('sex', ['M', 'F']);
            $table->date('birthdate');
            $table->string('contact_number', 30)->nullable();
            $table->text('notes')->nullable();
            $table->integer('company_id')->nullable()->default(null);
            $table->integer('created_by')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->index('full_name');
            $table->index('birthdate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
