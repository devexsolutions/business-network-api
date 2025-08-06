<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OneToOneFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'met_with_user_id',
        'invited_by_user_id',
        'group_name',
        'location',
        'meeting_date',
        'conversation_topics',
        'attendees',
        'meeting_type',
        'duration_minutes',
        'outcome',
        'follow_up_actions',
        'business_opportunities',
        'referrals_given',
        'referrals_received',
        'future_meeting_planned',
        'next_meeting_date',
        'notes',
        'attachments',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'next_meeting_date' => 'date',
            'attendees' => 'array',
            'attachments' => 'array',
            'future_meeting_planned' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metWithUser()
    {
        return $this->belongsTo(User::class, 'met_with_user_id');
    }

    public function invitedByUser()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFollowUpPending($query)
    {
        return $query->where('status', 'follow_up_pending');
    }

    public function scopeByGroup($query, $groupName)
    {
        return $query->where('group_name', 'like', "%{$groupName}%");
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('meeting_date', [$startDate, $endDate]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->orWhere('met_with_user_id', $userId);
    }

    public function scopeByOutcome($query, $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeWithFutureMeeting($query)
    {
        return $query->where('future_meeting_planned', true);
    }

    // Helper methods
    public function getOutcomeText()
    {
        return match($this->outcome) {
            'excellent' => 'Excelente',
            'good' => 'Buena',
            'average' => 'Regular',
            'poor' => 'Mala',
            'no_show' => 'No Asistió',
            default => 'No definido'
        };
    }

    public function getOutcomeColor()
    {
        return match($this->outcome) {
            'excellent' => '#28a745',  // Verde
            'good' => '#20c997',       // Verde claro
            'average' => '#ffc107',    // Amarillo
            'poor' => '#fd7e14',       // Naranja
            'no_show' => '#dc3545',    // Rojo
            default => '#6c757d'       // Gris
        };
    }

    public function getTypeText()
    {
        return match($this->meeting_type) {
            'one_to_one' => 'Uno a Uno',
            'group_meeting' => 'Reunión Grupal',
            'coffee_chat' => 'Café Informal',
            'business_lunch' => 'Almuerzo de Negocios',
            'other' => 'Otro',
            default => 'No definido'
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'completed' => 'Completado',
            'follow_up_pending' => 'Seguimiento Pendiente',
            default => 'Desconocido'
        };
    }

    public function getDurationText()
    {
        if (!$this->duration_minutes) {
            return 'No especificada';
        }
        
        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
        
        return $minutes . ' minutos';
    }

    public function hasBusinessOpportunities()
    {
        return !empty($this->business_opportunities);
    }

    public function hasReferrals()
    {
        return !empty($this->referrals_given) || !empty($this->referrals_received);
    }

    public function needsFollowUp()
    {
        return $this->status === 'follow_up_pending' || 
               $this->future_meeting_planned || 
               !empty($this->follow_up_actions);
    }
}