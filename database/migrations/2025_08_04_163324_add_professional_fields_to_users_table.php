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
        Schema::table('users', function (Blueprint $table) {
            // Información de contacto profesional
            $table->string('direct_phone')->nullable()->after('phone');
            $table->string('fax')->nullable()->after('direct_phone');
            $table->string('toll_free_phone')->nullable()->after('fax');
            
            // Información fiscal y legal
            $table->string('tax_address')->nullable()->after('location');
            $table->string('tax_id')->nullable()->after('tax_address'); // CIF/NIF/NIE
            $table->enum('tax_id_type', ['CIF', 'NIF', 'NIE'])->nullable()->after('tax_id');
            
            // Información profesional específica
            $table->string('specialty')->nullable()->after('position');
            $table->string('professional_group')->nullable()->after('specialty');
            $table->enum('membership_status', ['active', 'inactive', 'pending', 'suspended'])->default('pending')->after('is_active');
            $table->date('membership_renewal_date')->nullable()->after('membership_status');
            
            // Descripción del negocio
            $table->text('business_description')->nullable()->after('bio');
            $table->json('keywords')->nullable()->after('business_description'); // Palabras clave separadas por comas
            
            // Configuración de perfil
            $table->boolean('profile_completed')->default(false)->after('keywords');
            $table->timestamp('last_profile_update')->nullable()->after('profile_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'direct_phone',
                'fax',
                'toll_free_phone',
                'tax_address',
                'tax_id',
                'tax_id_type',
                'specialty',
                'professional_group',
                'membership_status',
                'membership_renewal_date',
                'business_description',
                'keywords',
                'profile_completed',
                'last_profile_update'
            ]);
        });
    }
};
