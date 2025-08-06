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
        Schema::create('one_to_one_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade'); // Quien solicita
            $table->foreignId('requested_id')->constrained('users')->onDelete('cascade'); // A quien se solicita
            $table->datetime('meeting_date'); // Fecha y hora propuesta
            $table->datetime('confirmed_date')->nullable(); // Fecha confirmada
            $table->string('location')->nullable(); // Ubicación de la reunión
            $table->enum('meeting_type', ['in_person', 'virtual', 'phone'])->default('in_person');
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'cancelled'])->default('pending');
            $table->text('purpose')->nullable(); // Propósito de la reunión
            $table->text('agenda')->nullable(); // Agenda propuesta
            $table->text('notes')->nullable(); // Notas adicionales
            $table->text('requester_notes')->nullable(); // Notas del solicitante
            $table->text('requested_notes')->nullable(); // Notas del solicitado
            $table->json('contact_info')->nullable(); // Info de contacto adicional
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_meetings');
    }
};
