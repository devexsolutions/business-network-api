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
        Schema::create('one_to_one_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que registra el seguimiento
            $table->foreignId('met_with_user_id')->constrained('users')->onDelete('cascade'); // Con quién se reunió
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->onDelete('set null'); // Invitado por
            $table->string('group_name')->nullable(); // Grupo BNI (ej: BNI EXS Supera)
            $table->string('location'); // Lugar de la reunión
            $table->date('meeting_date'); // Fecha de la reunión
            $table->text('conversation_topics'); // Temas de conversación
            $table->json('attendees')->nullable(); // Otros asistentes si los hay
            $table->enum('meeting_type', ['one_to_one', 'group_meeting', 'coffee_chat', 'business_lunch', 'other'])->default('one_to_one');
            $table->integer('duration_minutes')->nullable(); // Duración en minutos
            $table->enum('outcome', ['excellent', 'good', 'average', 'poor', 'no_show'])->default('good');
            $table->text('follow_up_actions')->nullable(); // Acciones de seguimiento acordadas
            $table->text('business_opportunities')->nullable(); // Oportunidades de negocio identificadas
            $table->text('referrals_given')->nullable(); // Referencias dadas
            $table->text('referrals_received')->nullable(); // Referencias recibidas
            $table->boolean('future_meeting_planned')->default(false); // ¿Se planificó reunión futura?
            $table->date('next_meeting_date')->nullable(); // Fecha de próxima reunión
            $table->text('notes')->nullable(); // Notas adicionales
            $table->json('attachments')->nullable(); // Archivos adjuntos
            $table->enum('status', ['draft', 'completed', 'follow_up_pending'])->default('completed');
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'meeting_date']);
            $table->index(['met_with_user_id', 'meeting_date']);
            $table->index(['group_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_follow_ups');
    }
};
