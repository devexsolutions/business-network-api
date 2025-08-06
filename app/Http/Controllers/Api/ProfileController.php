<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['company']);
        
        return response()->json([
            'profile' => $user,
            'completion_percentage' => $user->getProfileCompletionPercentage(),
            'contact_info' => $user->getFullContactInfo(),
            'is_active_member' => $user->isActiveMember(),
            'membership_expiring' => $user->isMembershipExpiring(),
        ]);
    }

    public function updateBasicInfo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'direct_phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'toll_free_phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($request->only([
            'name', 'phone', 'direct_phone', 'fax', 'toll_free_phone', 
            'location', 'bio'
        ]));

        $user->updateProfileCompletion();

        return response()->json([
            'message' => 'Información básica actualizada exitosamente',
            'profile' => $user->fresh(),
            'completion_percentage' => $user->getProfileCompletionPercentage(),
        ]);
    }

    public function updateProfessionalInfo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'position' => 'sometimes|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'professional_group' => 'nullable|string|max:255',
            'business_description' => 'nullable|string|max:2000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'linkedin_url' => 'nullable|url',
            'website_url' => 'nullable|url',
        ]);

        $user->update($request->only([
            'position', 'specialty', 'professional_group', 'business_description',
            'skills', 'interests', 'keywords', 'linkedin_url', 'website_url'
        ]));

        $user->updateProfileCompletion();

        return response()->json([
            'message' => 'Información profesional actualizada exitosamente',
            'profile' => $user->fresh(),
            'completion_percentage' => $user->getProfileCompletionPercentage(),
        ]);
    }

    public function updateTaxInfo(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'tax_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:20',
            'tax_id_type' => ['nullable', Rule::in(['CIF', 'NIF', 'NIE'])],
        ]);

        $user->update($request->only([
            'tax_address', 'tax_id', 'tax_id_type'
        ]));

        $user->updateProfileCompletion();

        return response()->json([
            'message' => 'Información fiscal actualizada exitosamente',
            'profile' => $user->fresh(),
        ]);
    }

    public function updateMembershipInfo(Request $request)
    {
        $user = $request->user();

        // Solo administradores pueden actualizar información de membresía
        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar información de membresía'
            ], 403);
        }

        $request->validate([
            'membership_status' => ['sometimes', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'membership_renewal_date' => 'nullable|date|after:today',
        ]);

        $user->update($request->only([
            'membership_status', 'membership_renewal_date'
        ]));

        return response()->json([
            'message' => 'Información de membresía actualizada exitosamente',
            'profile' => $user->fresh(),
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar && file_exists(public_path('storage/' . $user->avatar))) {
                unlink(public_path('storage/' . $user->avatar));
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $avatarPath]);

            $user->updateProfileCompletion();

            return response()->json([
                'message' => 'Avatar actualizado exitosamente',
                'avatar_url' => asset('storage/' . $avatarPath),
                'profile' => $user->fresh(),
            ]);
        }

        return response()->json([
            'message' => 'No se pudo subir el avatar'
        ], 400);
    }

    public function getProfileStats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'profile_completion' => $user->getProfileCompletionPercentage(),
            'connections_count' => $user->connections()->count(),
            'posts_count' => $user->posts()->count(),
            'events_created' => $user->events()->count(),
            'events_attended' => $user->eventAttendances()->where('status', 'attended')->count(),
            'membership_status' => $user->membership_status,
            'is_active_member' => $user->isActiveMember(),
            'membership_expiring' => $user->isMembershipExpiring(),
            'days_until_renewal' => $user->membership_renewal_date 
                ? $user->membership_renewal_date->diffInDays(now()) 
                : null,
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function searchByKeywords(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
        ]);

        $query = User::with('company')
            ->where('is_active', true)
            ->where('membership_status', 'active')
            ->where('id', '!=', $request->user()->id);

        // Búsqueda por palabras clave
        $keywords = explode(',', $request->keywords);
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                $q->orWhere('business_description', 'like', "%{$keyword}%")
                  ->orWhereJsonContains('keywords', $keyword)
                  ->orWhereJsonContains('skills', $keyword)
                  ->orWhere('specialty', 'like', "%{$keyword}%");
            }
        });

        // Filtros adicionales
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('specialty')) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        $users = $query->paginate(20);

        return response()->json($users);
    }
}