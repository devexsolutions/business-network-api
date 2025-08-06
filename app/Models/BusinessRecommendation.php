<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'recommender_id',
        'recommended_to_id',
        'recommended_user_id',
        'recommendation_date',
        'business_description',
        'why_recommended',
        'contact_info',
        'recommendation_type',
        'priority_level',
        'status',
        'follow_up_notes',
        'tags',
        'contacted_at',
        'completed_at',
        'is_mutual',
        'estimated_value',
        'outcome_notes',
    ];

    protected function casts(): array
    {
        return [
            'recommendation_date' => 'date',
            'contact_info' => 'array',
            'tags' => 'array',
            'contacted_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_mutual' => 'boolean',
            'estimated_value' => 'decimal:2',
        ];
    }

    // Relationships
    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommender_id');
    }

    public function recommendedTo()
    {
        return $this->belongsTo(User::class, 'recommended_to_id');
    }

    public function recommendedUser()
    {
        return $this->belongsTo(User::class, 'recommended_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['business_done', 'not_interested', 'no_response']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('recommendation_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recommender_id', $userId)
                    ->orWhere('recommended_to_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('recommendation_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function markAsContacted()
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function markAsCompleted($outcome = null)
    {
        $updateData = [
            'status' => 'business_done',
            'completed_at' => now(),
        ];
        
        if ($outcome) {
            $updateData['outcome_notes'] = $outcome;
        }
        
        $this->update($updateData);
    }

    public function getStatusText()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'contacted' => 'Contactado',
            'meeting_scheduled' => 'Reunión Programada',
            'business_done' => 'Negocio Realizado',
            'not_interested' => 'No Interesado',
            'no_response' => 'Sin Respuesta',
            default => 'Desconocido'
        };
    }

    public function getTypeText()
    {
        return match($this->recommendation_type) {
            'business_opportunity' => 'Oportunidad de Negocio',
            'service_provider' => 'Proveedor de Servicios',
            'potential_client' => 'Cliente Potencial',
            'partnership' => 'Sociedad',
            'other' => 'Otro',
            default => 'No definido'
        };
    }

    public function getPriorityText()
    {
        return match($this->priority_level) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => 'No definida'
        };
    }

    public function getPriorityColor()
    {
        return match($this->priority_level) {
            'low' => '#28a745',      // Verde
            'medium' => '#ffc107',   // Amarillo
            'high' => '#fd7e14',     // Naranja
            'urgent' => '#dc3545',   // Rojo
            default => '#6c757d'     // Gris
        };
    }
}