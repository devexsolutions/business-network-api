<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'company_description',
        'industry',
        'size',
        'employees_count',
        'founded_year',
        'website',
        'email',
        'phone',
        'fax',
        'toll_free_phone',
        'tax_id',
        'tax_address',
        'logo',
        'cover_image',
        'address',
        'social_links',
        'business_hours',
        'services',
        'membership_type',
        'membership_start_date',
        'membership_end_date',
        'membership_status',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'social_links' => 'array',
            'business_hours' => 'array',
            'services' => 'array',
            'membership_start_date' => 'date',
            'membership_end_date' => 'date',
            'founded_year' => 'integer',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    // Helper methods
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function isActiveMember()
    {
        return $this->membership_status === 'active' && 
               $this->membership_end_date && 
               $this->membership_end_date->isFuture();
    }

    public function isMembershipExpiring($days = 30)
    {
        if (!$this->membership_end_date) {
            return false;
        }
        
        return $this->membership_end_date->diffInDays(now()) <= $days;
    }

    public function getContactInfo()
    {
        return [
            'phone' => $this->phone,
            'fax' => $this->fax,
            'toll_free_phone' => $this->toll_free_phone,
            'email' => $this->email,
            'website' => $this->website,
        ];
    }

    public function getBusinessInfo()
    {
        return [
            'description' => $this->company_description ?: $this->description,
            'industry' => $this->industry,
            'size' => $this->size,
            'employees_count' => $this->employees_count,
            'founded_year' => $this->founded_year,
            'services' => $this->services,
            'business_hours' => $this->business_hours,
        ];
    }
}
