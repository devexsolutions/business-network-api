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
        Schema::create('referral_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('one_to_one_meetings')->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade'); // Quien da la referencia
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade'); // Para quien es la referencia
            $table->date('referral_date'); // Fecha de la referencia
            $table->text('referral_description'); // Descripción del negocio/servicio
            $table->enum('referral_type', ['internal', 'external'])->default('external'); // Interna/Externa
            $table->json('referral_status')->nullable(); // Estado: tarjeta entregada, les dije que llamarías, etc.
            $table->string('contact_name')->nullable(); // Nombre del contacto referido
            $table->string('contact_phone')->nullable(); // Teléfono del contacto
            $table->string('contact_email')->nullable(); // Email del contacto
            $table->text('contact_address')->nullable(); // Dirección del contacto
            $table->text('comments')->nullable(); // Comentarios adicionales
            $table->enum('interest_level', ['very_low', 'low', 'medium', 'high', 'very_high'])->default('medium'); // Grado de interés
            $table->enum('status', ['draft', 'sent', 'received', 'completed'])->default('draft');
            $table->json('follow_up_actions')->nullable(); // Acciones de seguimiento
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_cards');
    }
};
