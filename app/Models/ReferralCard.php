<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'from_user_id',
        'to_user_id',
        'referral_date',
        'referral_description',
        'referral_type',
        'referral_status',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_address',
        'comments',
        'interest_level',
        'status',
        'follow_up_actions',
        'sent_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'referral_date' => 'date',
            'referral_status' => 'array',
            'follow_up_actions' => 'array',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    // Relationships
    public function meeting()
    {
        return $this->belongsTo(OneToOneMeeting::class, 'meeting_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByInterestLevel($query, $level)
    {
        return $query->where('interest_level', $level);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('referral_type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('referral_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function send()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsReceived()
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
    }

    public function getInterestLevelColor()
    {
        return match($this->interest_level) {
            'very_low' => '#ff4444',    // Rojo
            'low' => '#ff8800',         // Naranja
            'medium' => '#ffaa00',      // Amarillo
            'high' => '#88cc00',        // Verde claro
            'very_high' => '#00aa00',   // Verde
            default => '#cccccc'        // Gris
        };
    }

    public function getInterestLevelText()
    {
        return match($this->interest_level) {
            'very_low' => 'Muy Bajo',
            'low' => 'Bajo',
            'medium' => 'Medio',
            'high' => 'Alto',
            'very_high' => 'Muy Alto',
            default => 'No definido'
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'sent' => 'Enviado',
            'received' => 'Recibido',
            'completed' => 'Completado',
            default => 'Desconocido'
        };
    }

    public function getTypeText()
    {
        return match($this->referral_type) {
            'internal' => 'Interna',
            'external' => 'Externa',
            default => 'No definido'
        };
    }
}