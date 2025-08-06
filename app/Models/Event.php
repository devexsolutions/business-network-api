<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'description',
        'type',
        'format',
        'location',
        'start_date',
        'end_date',
        'max_attendees',
        'price',
        'image',
        'tags',
        'is_published',
        'requires_approval',
    ];

    protected function casts(): array
    {
        return [
            'location' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'price' => 'decimal:2',
            'tags' => 'array',
            'is_published' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function attendeeUsers()
    {
        return $this->belongsToMany(User::class, 'event_attendees')
            ->withPivot('status', 'registered_at', 'attended_at')
            ->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isUpcoming()
    {
        return $this->start_date > now();
    }

    public function hasAvailableSpots()
    {
        if (!$this->max_attendees) {
            return true;
        }
        
        return $this->attendees()->where('status', 'registered')->count() < $this->max_attendees;
    }
}
