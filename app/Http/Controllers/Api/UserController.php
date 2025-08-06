<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('company')
            ->where('is_active', true)
            ->where('id', '!=', $request->user()->id);

        // Filtros de búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('skills')) {
            $skills = explode(',', $request->skills);
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhereJsonContains('skills', trim($skill));
                }
            });
        }

        $users = $query->paginate(20);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load(['company', 'posts' => function ($query) {
                $query->published()->latest()->take(5);
            }]),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'linkedin_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'location' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'interests' => 'nullable|array',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $user->update($request->only([
            'name', 'phone', 'position', 'bio', 'linkedin_url',
            'website_url', 'location', 'skills', 'interests', 'company_id'
        ]));

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->load('company'),
        ]);
    }

    public function suggestions(Request $request)
    {
        $user = $request->user();
        
        // Obtener usuarios sugeridos basados en empresa, skills, intereses
        $suggestions = User::with('company')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('addressee_id')
                    ->from('connections')
                    ->where('requester_id', $user->id);
            })
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('requester_id')
                    ->from('connections')
                    ->where('addressee_id', $user->id);
            })
            ->limit(10)
            ->get();

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}