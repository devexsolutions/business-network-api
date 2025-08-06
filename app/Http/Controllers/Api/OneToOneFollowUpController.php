<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OneToOneFollowUp;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OneToOneFollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OneToOneFollowUp::with(['user', 'metWithUser', 'invitedByUser'])
                                 ->forUser($user->id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('outcome')) {
            $query->byOutcome($request->outcome);
        }

        if ($request->has('group')) {
            $query->byGroup($request->group);
        }

        if ($request->has('meeting_type')) {
            $query->where('meeting_type', $request->meeting_type);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->byDateRange($startDate, $endDate);
        }

        // Filtro por seguimiento pendiente
        if ($request->has('needs_follow_up') && $request->needs_follow_up) {
            $query->followUpPending();
        }

        // Filtro por reuniones futuras planificadas
        if ($request->has('future_meetings') && $request->future_meetings) {
            $query->withFutureMeeting();
        }

        $followUps = $query->orderBy('meeting_date', 'desc')->paginate(20);

        // Agregar información adicional
        $followUps->getCollection()->transform(function ($followUp) use ($user) {
            $followUp->is_my_follow_up = $followUp->user_id === $user->id;
            $followUp->outcome_text = $followUp->getOutcomeText();
            $followUp->outcome_color = $followUp->getOutcomeColor();
            $followUp->type_text = $followUp->getTypeText();
            $followUp->status_text = $followUp->getStatusText();
            $followUp->duration_text = $followUp->getDurationText();
            $followUp->has_business_opportunities = $followUp->hasBusinessOpportunities();
            $followUp->has_referrals = $followUp->hasReferrals();
            $followUp->needs_follow_up = $followUp->needsFollowUp();
            return $followUp;
        });

        return response()->json($followUps);
    }

    public function store(Request $request)
    {
        $request->validate([
            'met_with_user_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'invited_by_user_id' => 'nullable|exists:users,id',
            'group_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'conversation_topics' => 'required|string|max:2000',
            'attendees' => 'nullable|array',
            'meeting_type' => 'in:one_to_one,group_meeting,coffee_chat,business_lunch,other',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'outcome' => 'in:excellent,good,average,poor,no_show',
            'follow_up_actions' => 'nullable|string|max:1000',
            'business_opportunities' => 'nullable|string|max:1000',
            'referrals_given' => 'nullable|string|max:1000',
            'referrals_received' => 'nullable|string|max:1000',
            'future_meeting_planned' => 'boolean',
            'next_meeting_date' => 'nullable|date|after:meeting_date',
            'notes' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
        ]);

        $user = $request->user();

        // Verificar que el usuario con quien se reunió esté activo
        $metWithUser = User::findOrFail($request->met_with_user_id);
        if (!$metWithUser->is_active || $metWithUser->membership_status !== 'active') {
            return response()->json([
                'message' => 'El usuario con quien te reuniste no está activo'
            ], 422);
        }

        $followUp = OneToOneFollowUp::create([
            'user_id' => $user->id,
            'met_with_user_id' => $request->met_with_user_id,
            'invited_by_user_id' => $request->invited_by_user_id,
            'group_name' => $request->group_name,
            'location' => $request->location,
            'meeting_date' => $request->meeting_date,
            'conversation_topics' => $request->conversation_topics,
            'attendees' => $request->attendees,
            'meeting_type' => $request->meeting_type ?? 'one_to_one',
            'duration_minutes' => $request->duration_minutes,
            'outcome' => $request->outcome ?? 'good',
            'follow_up_actions' => $request->follow_up_actions,
            'business_opportunities' => $request->business_opportunities,
            'referrals_given' => $request->referrals_given,
            'referrals_received' => $request->referrals_received,
            'future_meeting_planned' => $request->future_meeting_planned ?? false,
            'next_meeting_date' => $request->next_meeting_date,
            'notes' => $request->notes,
            'attachments' => $request->attachments,
            'status' => !empty($request->follow_up_actions) ? 'follow_up_pending' : 'completed',
        ]);

        return response()->json([
            'message' => 'Seguimiento uno a uno creado exitosamente',
            'follow_up' => $followUp->load(['user', 'metWithUser', 'invitedByUser']),
        ], 201);
    }

    public function show(OneToOneFollowUp $oneToOneFollowUp)
    {
        $user = request()->user();
        
        // Verificar que el usuario tenga acceso a este seguimiento
        if ($oneToOneFollowUp->user_id !== $user->id && 
            $oneToOneFollowUp->met_with_user_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes acceso a este seguimiento'
            ], 403);
        }

        $oneToOneFollowUp->load(['user', 'metWithUser', 'invitedByUser']);
        
        return response()->json([
            'follow_up' => $oneToOneFollowUp,
            'is_my_follow_up' => $oneToOneFollowUp->user_id === $user->id,
            'outcome_text' => $oneToOneFollowUp->getOutcomeText(),
            'outcome_color' => $oneToOneFollowUp->getOutcomeColor(),
            'type_text' => $oneToOneFollowUp->getTypeText(),
            'status_text' => $oneToOneFollowUp->getStatusText(),
            'duration_text' => $oneToOneFollowUp->getDurationText(),
            'has_business_opportunities' => $oneToOneFollowUp->hasBusinessOpportunities(),
            'has_referrals' => $oneToOneFollowUp->hasReferrals(),
            'needs_follow_up' => $oneToOneFollowUp->needsFollowUp(),
        ]);
    }

    public function update(Request $request, OneToOneFollowUp $oneToOneFollowUp)
    {
        $user = $request->user();
        
        // Solo el creador puede modificar el seguimiento
        if ($oneToOneFollowUp->user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el creador puede modificar este seguimiento'
            ], 403);
        }

        $request->validate([
            'group_name' => 'nullable|string|max:255',
            'location' => 'sometimes|string|max:255',
            'meeting_date' => 'sometimes|date',
            'conversation_topics' => 'sometimes|string|max:2000',
            'attendees' => 'nullable|array',
            'meeting_type' => 'in:one_to_one,group_meeting,coffee_chat,business_lunch,other',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'outcome' => 'in:excellent,good,average,poor,no_show',
            'follow_up_actions' => 'nullable|string|max:1000',
            'business_opportunities' => 'nullable|string|max:1000',
            'referrals_given' => 'nullable|string|max:1000',
            'referrals_received' => 'nullable|string|max:1000',
            'future_meeting_planned' => 'boolean',
            'next_meeting_date' => 'nullable|date|after:meeting_date',
            'notes' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
            'status' => 'in:draft,completed,follow_up_pending',
        ]);

        $updateData = $request->only([
            'group_name', 'location', 'meeting_date', 'conversation_topics',
            'attendees', 'meeting_type', 'duration_minutes', 'outcome',
            'follow_up_actions', 'business_opportunities', 'referrals_given',
            'referrals_received', 'future_meeting_planned', 'next_meeting_date',
            'notes', 'attachments', 'status'
        ]);

        // Auto-determinar status si no se especifica
        if (!$request->has('status')) {
            if (!empty($request->follow_up_actions)) {
                $updateData['status'] = 'follow_up_pending';
            } else {
                $updateData['status'] = 'completed';
            }
        }

        $oneToOneFollowUp->update($updateData);

        return response()->json([
            'message' => 'Seguimiento actualizado exitosamente',
            'follow_up' => $oneToOneFollowUp->fresh()->load(['user', 'metWithUser', 'invitedByUser']),
        ]);
    }

    public function destroy(OneToOneFollowUp $oneToOneFollowUp)
    {
        $user = request()->user();
        
        if ($oneToOneFollowUp->user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el creador puede eliminar este seguimiento'
            ], 403);
        }

        $oneToOneFollowUp->delete();

        return response()->json([
            'message' => 'Seguimiento eliminado exitosamente',
        ]);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_follow_ups' => $user->followUps()->count(),
            'meetings_with_me' => $user->meetingsWithMe()->count(),
            'this_month_meetings' => $user->followUps()
                ->byDateRange(now()->startOfMonth(), now()->endOfMonth())
                ->count(),
            'excellent_outcomes' => $user->followUps()->byOutcome('excellent')->count(),
            'good_outcomes' => $user->followUps()->byOutcome('good')->count(),
            'follow_ups_pending' => $user->followUps()->followUpPending()->count(),
            'future_meetings_planned' => $user->followUps()->withFutureMeeting()->count(),
            'business_opportunities_identified' => $user->followUps()
                ->whereNotNull('business_opportunities')
                ->where('business_opportunities', '!=', '')
                ->count(),
            'referrals_given_count' => $user->followUps()
                ->whereNotNull('referrals_given')
                ->where('referrals_given', '!=', '')
                ->count(),
            'referrals_received_count' => $user->followUps()
                ->whereNotNull('referrals_received')
                ->where('referrals_received', '!=', '')
                ->count(),
            'average_meeting_duration' => $user->followUps()
                ->whereNotNull('duration_minutes')
                ->avg('duration_minutes'),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function getUpcomingMeetings(Request $request)
    {
        $user = $request->user();
        
        $upcomingMeetings = $user->followUps()
            ->withFutureMeeting()
            ->whereNotNull('next_meeting_date')
            ->where('next_meeting_date', '>=', now())
            ->with(['metWithUser'])
            ->orderBy('next_meeting_date')
            ->get();

        return response()->json([
            'upcoming_meetings' => $upcomingMeetings,
        ]);
    }

    public function getBusinessOpportunities(Request $request)
    {
        $user = $request->user();
        
        $opportunities = $user->followUps()
            ->whereNotNull('business_opportunities')
            ->where('business_opportunities', '!=', '')
            ->with(['metWithUser'])
            ->orderBy('meeting_date', 'desc')
            ->get()
            ->map(function ($followUp) {
                return [
                    'id' => $followUp->id,
                    'meeting_date' => $followUp->meeting_date,
                    'met_with' => $followUp->metWithUser,
                    'business_opportunities' => $followUp->business_opportunities,
                    'outcome' => $followUp->outcome,
                    'follow_up_actions' => $followUp->follow_up_actions,
                ];
            });

        return response()->json([
            'business_opportunities' => $opportunities,
        ]);
    }

    public function getReferralsSummary(Request $request)
    {
        $user = $request->user();
        
        $referralsGiven = $user->followUps()
            ->whereNotNull('referrals_given')
            ->where('referrals_given', '!=', '')
            ->with(['metWithUser'])
            ->get()
            ->map(function ($followUp) {
                return [
                    'meeting_date' => $followUp->meeting_date,
                    'met_with' => $followUp->metWithUser,
                    'referrals' => $followUp->referrals_given,
                ];
            });

        $referralsReceived = $user->followUps()
            ->whereNotNull('referrals_received')
            ->where('referrals_received', '!=', '')
            ->with(['metWithUser'])
            ->get()
            ->map(function ($followUp) {
                return [
                    'meeting_date' => $followUp->meeting_date,
                    'met_with' => $followUp->metWithUser,
                    'referrals' => $followUp->referrals_received,
                ];
            });

        return response()->json([
            'referrals_given' => $referralsGiven,
            'referrals_received' => $referralsReceived,
        ]);
    }
}