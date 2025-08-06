<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OneToOneMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'requested_id',
        'meeting_date',
        'confirmed_date',
        'location',
        'meeting_type',
        'status',
        'purpose',
        'agenda',
        'notes',
        'requester_notes',
        'requested_notes',
        'contact_info',
        'priority',
        'accepted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'datetime',
            'confirmed_date' => 'datetime',
            'contact_info' => 'array',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requested()
    {
        return $this->belongsTo(User::class, 'requested_id');
    }

    public function referralCards()
    {
        return $this->hasMany(ReferralCard::class, 'meeting_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('meeting_date', '>', now())
                    ->whereIn('status', ['pending', 'accepted']);
    }

    public function scopePast($query)
    {
        return $query->where('meeting_date', '<', now())
                    ->orWhere('status', 'completed');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('meeting_date', [$startDate, $endDate]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('requester_id', $userId)
                    ->orWhere('requested_id', $userId);
    }

    // Helper methods
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline()
    {
        $this->update(['status' => 'declined']);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isUpcoming()
    {
        return $this->meeting_date > now() && in_array($this->status, ['pending', 'accepted']);
    }

    public function isPast()
    {
        return $this->meeting_date < now() || $this->status === 'completed';
    }

    public function canBeModifiedBy(User $user)
    {
        return $this->requester_id === $user->id || $this->requested_id === $user->id;
    }

    public function getOtherParticipant(User $user)
    {
        if ($this->requester_id === $user->id) {
            return $this->requested;
        }
        
        return $this->requester;
    }

    public function getDurationInMinutes()
    {
        if (!$this->confirmed_date) {
            return 60; // Default 1 hour
        }
        
        // Assuming meetings are 1 hour by default, can be extended
        return 60;
    }
}