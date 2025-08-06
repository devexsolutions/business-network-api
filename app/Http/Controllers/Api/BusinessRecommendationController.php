<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessRecommendation;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BusinessRecommendationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = BusinessRecommendation::with(['recommender', 'recommendedTo', 'recommendedUser'])
                                      ->forUser($user->id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->byDateRange($startDate, $endDate);
        }

        // Filtro por dirección (dadas o recibidas)
        if ($request->has('direction')) {
            if ($request->direction === 'given') {
                $query->where('recommender_id', $user->id);
            } elseif ($request->direction === 'received') {
                $query->where('recommended_to_id', $user->id);
            } elseif ($request->direction === 'about_me') {
                $query->where('recommended_user_id', $user->id);
            }
        }

        $recommendations = $query->orderBy('recommendation_date', 'desc')->paginate(20);

        // Agregar información adicional
        $recommendations->getCollection()->transform(function ($recommendation) use ($user) {
            $recommendation->is_recommender = $recommendation->recommender_id === $user->id;
            $recommendation->is_recommended_to = $recommendation->recommended_to_id === $user->id;
            $recommendation->is_about_me = $recommendation->recommended_user_id === $user->id;
            $recommendation->status_text = $recommendation->getStatusText();
            $recommendation->type_text = $recommendation->getTypeText();
            $recommendation->priority_text = $recommendation->getPriorityText();
            $recommendation->priority_color = $recommendation->getPriorityColor();
            return $recommendation;
        });

        return response()->json($recommendations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'recommended_to_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'recommended_user_id' => 'required|exists:users,id|different:' . $request->user()->id . '|different:recommended_to_id',
            'recommendation_date' => 'required|date',
            'business_description' => 'required|string|max:2000',
            'why_recommended' => 'required|string|max:1000',
            'contact_info' => 'nullable|array',
            'recommendation_type' => 'in:business_opportunity,service_provider,potential_client,partnership,other',
            'priority_level' => 'in:low,medium,high,urgent',
            'tags' => 'nullable|array',
            'estimated_value' => 'nullable|numeric|min:0',
            'is_mutual' => 'boolean',
        ]);

        $user = $request->user();

        // Verificar que los usuarios estén activos y sean miembros
        $recommendedTo = User::findOrFail($request->recommended_to_id);
        $recommendedUser = User::findOrFail($request->recommended_user_id);

        if (!$recommendedTo->is_active || $recommendedTo->membership_status !== 'active') {
            return response()->json([
                'message' => 'El usuario al que recomiendas no está activo'
            ], 422);
        }

        if (!$recommendedUser->is_active || $recommendedUser->membership_status !== 'active') {
            return response()->json([
                'message' => 'El usuario recomendado no está activo'
            ], 422);
        }

        $recommendation = BusinessRecommendation::create([
            'recommender_id' => $user->id,
            'recommended_to_id' => $request->recommended_to_id,
            'recommended_user_id' => $request->recommended_user_id,
            'recommendation_date' => $request->recommendation_date,
            'business_description' => $request->business_description,
            'why_recommended' => $request->why_recommended,
            'contact_info' => $request->contact_info,
            'recommendation_type' => $request->recommendation_type ?? 'business_opportunity',
            'priority_level' => $request->priority_level ?? 'medium',
            'tags' => $request->tags,
            'estimated_value' => $request->estimated_value,
            'is_mutual' => $request->is_mutual ?? false,
        ]);

        return response()->json([
            'message' => 'Recomendación creada exitosamente',
            'recommendation' => $recommendation->load(['recommender', 'recommendedTo', 'recommendedUser']),
        ], 201);
    }

    public function show(BusinessRecommendation $businessRecommendation)
    {
        $user = request()->user();
        
        // Verificar que el usuario tenga acceso a esta recomendación
        if ($businessRecommendation->recommender_id !== $user->id && 
            $businessRecommendation->recommended_to_id !== $user->id &&
            $businessRecommendation->recommended_user_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes acceso a esta recomendación'
            ], 403);
        }

        $businessRecommendation->load(['recommender', 'recommendedTo', 'recommendedUser']);
        
        return response()->json([
            'recommendation' => $businessRecommendation,
            'is_recommender' => $businessRecommendation->recommender_id === $user->id,
            'is_recommended_to' => $businessRecommendation->recommended_to_id === $user->id,
            'is_about_me' => $businessRecommendation->recommended_user_id === $user->id,
            'status_text' => $businessRecommendation->getStatusText(),
            'type_text' => $businessRecommendation->getTypeText(),
            'priority_text' => $businessRecommendation->getPriorityText(),
            'priority_color' => $businessRecommendation->getPriorityColor(),
        ]);
    }

    public function update(Request $request, BusinessRecommendation $businessRecommendation)
    {
        $user = $request->user();
        
        // Solo el recomendador puede modificar la recomendación
        if ($businessRecommendation->recommender_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el recomendador puede modificar esta recomendación'
            ], 403);
        }

        $request->validate([
            'business_description' => 'sometimes|string|max:2000',
            'why_recommended' => 'sometimes|string|max:1000',
            'contact_info' => 'nullable|array',
            'recommendation_type' => 'in:business_opportunity,service_provider,potential_client,partnership,other',
            'priority_level' => 'in:low,medium,high,urgent',
            'tags' => 'nullable|array',
            'estimated_value' => 'nullable|numeric|min:0',
            'follow_up_notes' => 'nullable|string|max:1000',
            'outcome_notes' => 'nullable|string|max:1000',
        ]);

        $businessRecommendation->update($request->only([
            'business_description', 'why_recommended', 'contact_info',
            'recommendation_type', 'priority_level', 'tags',
            'estimated_value', 'follow_up_notes', 'outcome_notes'
        ]));

        return response()->json([
            'message' => 'Recomendación actualizada exitosamente',
            'recommendation' => $businessRecommendation->fresh()->load(['recommender', 'recommendedTo', 'recommendedUser']),
        ]);
    }

    public function destroy(BusinessRecommendation $businessRecommendation)
    {
        $user = request()->user();
        
        if ($businessRecommendation->recommender_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el recomendador puede eliminar esta recomendación'
            ], 403);
        }

        // Solo se puede eliminar si está pendiente
        if ($businessRecommendation->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden eliminar recomendaciones pendientes'
            ], 422);
        }

        $businessRecommendation->delete();

        return response()->json([
            'message' => 'Recomendación eliminada exitosamente',
        ]);
    }

    public function markAsContacted(Request $request, BusinessRecommendation $businessRecommendation)
    {
        $user = $request->user();
        
        // Solo el usuario recomendado puede marcar como contactado
        if ($businessRecommendation->recommended_to_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el usuario recomendado puede marcar como contactado'
            ], 403);
        }

        $request->validate([
            'follow_up_notes' => 'nullable|string|max:1000',
        ]);

        $businessRecommendation->markAsContacted();
        
        if ($request->has('follow_up_notes')) {
            $businessRecommendation->update(['follow_up_notes' => $request->follow_up_notes]);
        }

        return response()->json([
            'message' => 'Recomendación marcada como contactada',
            'recommendation' => $businessRecommendation->fresh()->load(['recommender', 'recommendedTo', 'recommendedUser']),
        ]);
    }

    public function markAsCompleted(Request $request, BusinessRecommendation $businessRecommendation)
    {
        $user = $request->user();
        
        if ($businessRecommendation->recommended_to_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el usuario recomendado puede completar la recomendación'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:business_done,not_interested,no_response',
            'outcome_notes' => 'nullable|string|max:1000',
            'estimated_value' => 'nullable|numeric|min:0',
        ]);

        $updateData = [
            'status' => $request->status,
            'completed_at' => now(),
        ];

        if ($request->has('outcome_notes')) {
            $updateData['outcome_notes'] = $request->outcome_notes;
        }

        if ($request->has('estimated_value')) {
            $updateData['estimated_value'] = $request->estimated_value;
        }

        $businessRecommendation->update($updateData);

        return response()->json([
            'message' => 'Recomendación completada exitosamente',
            'recommendation' => $businessRecommendation->fresh()->load(['recommender', 'recommendedTo', 'recommendedUser']),
        ]);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_given' => $user->givenRecommendations()->count(),
            'total_received' => $user->receivedRecommendations()->count(),
            'recommendations_about_me' => $user->recommendationsAboutMe()->count(),
            'pending_given' => $user->givenRecommendations()->pending()->count(),
            'pending_received' => $user->receivedRecommendations()->pending()->count(),
            'completed_given' => $user->givenRecommendations()->completed()->count(),
            'completed_received' => $user->receivedRecommendations()->completed()->count(),
            'business_done' => $user->receivedRecommendations()->where('status', 'business_done')->count(),
            'total_estimated_value' => $user->receivedRecommendations()
                ->where('status', 'business_done')
                ->sum('estimated_value'),
            'this_month_given' => $user->givenRecommendations()
                ->byDateRange(now()->startOfMonth(), now()->endOfMonth())
                ->count(),
            'high_priority_pending' => $user->receivedRecommendations()
                ->pending()
                ->whereIn('priority_level', ['high', 'urgent'])
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function getRecommendationNetwork(Request $request)
    {
        $user = $request->user();
        
        // Obtener usuarios más recomendados por mí
        $mostRecommendedUsers = $user->givenRecommendations()
            ->selectRaw('recommended_user_id, COUNT(*) as recommendation_count')
            ->groupBy('recommended_user_id')
            ->orderBy('recommendation_count', 'desc')
            ->limit(10)
            ->with('recommendedUser')
            ->get();

        // Obtener usuarios que más me recomiendan
        $topRecommenders = $user->recommendationsAboutMe()
            ->selectRaw('recommender_id, COUNT(*) as recommendation_count')
            ->groupBy('recommender_id')
            ->orderBy('recommendation_count', 'desc')
            ->limit(10)
            ->with('recommender')
            ->get();

        return response()->json([
            'most_recommended_users' => $mostRecommendedUsers,
            'top_recommenders' => $topRecommenders,
        ]);
    }
}