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
        Schema::create('business_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommender_id')->constrained('users')->onDelete('cascade'); // Usuario A que recomienda
            $table->foreignId('recommended_to_id')->constrained('users')->onDelete('cascade'); // Usuario B al que se recomienda
            $table->foreignId('recommended_user_id')->constrained('users')->onDelete('cascade'); // Usuario C recomendado
            $table->date('recommendation_date');
            $table->text('business_description'); // Descripción del negocio/oportunidad
            $table->text('why_recommended'); // Por qué lo recomienda
            $table->json('contact_info')->nullable(); // Info de contacto del recomendado
            $table->enum('recommendation_type', ['business_opportunity', 'service_provider', 'potential_client', 'partnership', 'other'])->default('business_opportunity');
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'contacted', 'meeting_scheduled', 'business_done', 'not_interested', 'no_response'])->default('pending');
            $table->text('follow_up_notes')->nullable();
            $table->json('tags')->nullable(); // Etiquetas para categorizar
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_mutual')->default(false); // Si es recomendación mutua
            $table->decimal('estimated_value', 10, 2)->nullable(); // Valor estimado del negocio
            $table->text('outcome_notes')->nullable(); // Notas del resultado
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['recommender_id', 'recommendation_date']);
            $table->index(['recommended_to_id', 'status']);
            $table->index(['recommended_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_recommendations');
    }
};
