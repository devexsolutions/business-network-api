<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'direct_phone',
        'fax',
        'toll_free_phone',
        'position',
        'specialty',
        'professional_group',
        'bio',
        'business_description',
        'linkedin_url',
        'website_url',
        'avatar',
        'location',
        'tax_address',
        'tax_id',
        'tax_id_type',
        'skills',
        'interests',
        'keywords',
        'is_active',
        'membership_status',
        'membership_renewal_date',
        'profile_completed',
        'company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array',
            'interests' => 'array',
            'keywords' => 'array',
            'is_active' => 'boolean',
            'profile_completed' => 'boolean',
            'membership_renewal_date' => 'date',
            'last_profile_update' => 'datetime',
        ];
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function eventAttendances()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    // Connection relationships
    public function sentConnections()
    {
        return $this->hasMany(Connection::class, 'requester_id');
    }

    public function receivedConnections()
    {
        return $this->hasMany(Connection::class, 'addressee_id');
    }

    public function connections()
    {
        return $this->belongsToMany(User::class, 'connections', 'requester_id', 'addressee_id')
            ->wherePivot('status', 'accepted')
            ->withPivot('status', 'accepted_at')
            ->withTimestamps();
    }

    // Helper methods
    public function isConnectedWith(User $user)
    {
        return $this->connections()->where('users.id', $user->id)->exists() ||
               $user->connections()->where('users.id', $this->id)->exists();
    }

    public function hasLikedPost(Post $post)
    {
        return $this->postLikes()->where('post_id', $post->id)->exists();
    }

    // Professional profile methods
    public function isActiveMember()
    {
        return $this->membership_status === 'active';
    }

    public function isMembershipExpiring($days = 30)
    {
        if (!$this->membership_renewal_date) {
            return false;
        }
        
        return $this->membership_renewal_date->diffInDays(now()) <= $days;
    }

    public function getProfileCompletionPercentage()
    {
        $requiredFields = [
            'name', 'email', 'phone', 'position', 'company_id',
            'bio', 'location', 'specialty', 'business_description'
        ];
        
        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }
        
        return round(($completedFields / count($requiredFields)) * 100);
    }

    public function updateProfileCompletion()
    {
        $percentage = $this->getProfileCompletionPercentage();
        $this->profile_completed = $percentage >= 80;
        $this->last_profile_update = now();
        $this->save();
    }

    public function getFullContactInfo()
    {
        return [
            'phone' => $this->phone,
            'direct_phone' => $this->direct_phone,
            'fax' => $this->fax,
            'toll_free_phone' => $this->toll_free_phone,
            'email' => $this->email,
        ];
    }

    // One-to-One Meeting relationships
    public function requestedMeetings()
    {
        return $this->hasMany(OneToOneMeeting::class, 'requester_id');
    }

    public function receivedMeetingRequests()
    {
        return $this->hasMany(OneToOneMeeting::class, 'requested_id');
    }

    public function allMeetings()
    {
        return OneToOneMeeting::where('requester_id', $this->id)
                             ->orWhere('requested_id', $this->id);
    }

    // Referral Card relationships
    public function sentReferrals()
    {
        return $this->hasMany(ReferralCard::class, 'from_user_id');
    }

    public function receivedReferrals()
    {
        return $this->hasMany(ReferralCard::class, 'to_user_id');
    }

    // Business Recommendation relationships
    public function givenRecommendations()
    {
        return $this->hasMany(BusinessRecommendation::class, 'recommender_id');
    }

    public function receivedRecommendations()
    {
        return $this->hasMany(BusinessRecommendation::class, 'recommended_to_id');
    }

    public function recommendationsAboutMe()
    {
        return $this->hasMany(BusinessRecommendation::class, 'recommended_user_id');
    }

    // One-to-One Follow-up relationships
    public function followUps()
    {
        return $this->hasMany(OneToOneFollowUp::class);
    }

    public function meetingsWithMe()
    {
        return $this->hasMany(OneToOneFollowUp::class, 'met_with_user_id');
    }

    public function invitedMeetings()
    {
        return $this->hasMany(OneToOneFollowUp::class, 'invited_by_user_id');
    }
}
