<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Información fiscal de la empresa
            $table->string('tax_id')->nullable()->after('email'); // CIF de la empresa
            $table->string('tax_address')->nullable()->after('tax_id');
            
            // Información de contacto adicional
            $table->string('fax')->nullable()->after('phone');
            $table->string('toll_free_phone')->nullable()->after('fax');
            
            // Información de membresía empresarial
            $table->enum('membership_type', ['basic', 'premium', 'enterprise'])->default('basic')->after('is_verified');
            $table->date('membership_start_date')->nullable()->after('membership_type');
            $table->date('membership_end_date')->nullable()->after('membership_start_date');
            $table->enum('membership_status', ['active', 'inactive', 'pending', 'suspended'])->default('pending')->after('membership_end_date');
            
            // Información adicional del negocio
            $table->json('business_hours')->nullable()->after('social_links'); // Horarios de atención
            $table->json('services')->nullable()->after('business_hours'); // Servicios que ofrece
            $table->text('company_description')->nullable()->after('description');
            $table->integer('employees_count')->nullable()->after('size');
            $table->year('founded_year')->nullable()->after('employees_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'tax_id',
                'tax_address',
                'fax',
                'toll_free_phone',
                'membership_type',
                'membership_start_date',
                'membership_end_date',
                'membership_status',
                'business_hours',
                'services',
                'company_description',
                'employees_count',
                'founded_year'
            ]);
        });
    }
};
