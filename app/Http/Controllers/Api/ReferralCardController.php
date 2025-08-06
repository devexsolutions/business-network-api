<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralCard;
use App\Models\OneToOneMeeting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReferralCardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ReferralCard::with(['meeting', 'fromUser', 'toUser'])
                            ->forUser($user->id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('interest_level')) {
            $query->byInterestLevel($request->interest_level);
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->byDateRange($startDate, $endDate);
        }

        // Filtro por dirección (enviadas o recibidas)
        if ($request->has('direction')) {
            if ($request->direction === 'sent') {
                $query->where('from_user_id', $user->id);
            } elseif ($request->direction === 'received') {
                $query->where('to_user_id', $user->id);
            }
        }

        $referrals = $query->orderBy('referral_date', 'desc')->paginate(20);

        // Agregar información adicional
        $referrals->getCollection()->transform(function ($referral) use ($user) {
            $referral->is_sender = $referral->from_user_id === $user->id;
            $referral->interest_level_color = $referral->getInterestLevelColor();
            $referral->interest_level_text = $referral->getInterestLevelText();
            $referral->status_text = $referral->getStatusText();
            $referral->type_text = $referral->getTypeText();
            return $referral;
        });

        return response()->json($referrals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:one_to_one_meetings,id',
            'to_user_id' => 'required|exists:users,id',
            'referral_date' => 'required|date',
            'referral_description' => 'required|string|max:2000',
            'referral_type' => 'in:internal,external',
            'referral_status' => 'nullable|array',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:500',
            'comments' => 'nullable|string|max:1000',
            'interest_level' => 'in:very_low,low,medium,high,very_high',
            'follow_up_actions' => 'nullable|array',
        ]);

        $user = $request->user();

        // Verificar que el usuario tenga acceso a la reunión
        $meeting = OneToOneMeeting::findOrFail($request->meeting_id);
        if (!$meeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes acceso a esta reunión'
            ], 403);
        }

        $referral = ReferralCard::create([
            'meeting_id' => $request->meeting_id,
            'from_user_id' => $user->id,
            'to_user_id' => $request->to_user_id,
            'referral_date' => $request->referral_date,
            'referral_description' => $request->referral_description,
            'referral_type' => $request->referral_type ?? 'external',
            'referral_status' => $request->referral_status,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'contact_address' => $request->contact_address,
            'comments' => $request->comments,
            'interest_level' => $request->interest_level ?? 'medium',
            'follow_up_actions' => $request->follow_up_actions,
        ]);

        return response()->json([
            'message' => 'Ficha de referencia creada exitosamente',
            'referral' => $referral->load(['meeting', 'fromUser', 'toUser']),
        ], 201);
    }

    public function show(ReferralCard $referralCard)
    {
        $user = request()->user();
        
        // Verificar que el usuario tenga acceso a esta ficha
        if ($referralCard->from_user_id !== $user->id && $referralCard->to_user_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes acceso a esta ficha de referencia'
            ], 403);
        }

        $referralCard->load(['meeting', 'fromUser', 'toUser']);
        
        return response()->json([
            'referral' => $referralCard,
            'is_sender' => $referralCard->from_user_id === $user->id,
            'interest_level_color' => $referralCard->getInterestLevelColor(),
            'interest_level_text' => $referralCard->getInterestLevelText(),
            'status_text' => $referralCard->getStatusText(),
            'type_text' => $referralCard->getTypeText(),
        ]);
    }

    public function update(Request $request, ReferralCard $referralCard)
    {
        $user = $request->user();
        
        // Solo el creador puede modificar la ficha
        if ($referralCard->from_user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el creador puede modificar esta ficha'
            ], 403);
        }

        // No se puede modificar si ya fue enviada
        if ($referralCard->status !== 'draft') {
            return response()->json([
                'message' => 'No se puede modificar una ficha que ya fue enviada'
            ], 422);
        }

        $request->validate([
            'referral_date' => 'sometimes|date',
            'referral_description' => 'sometimes|string|max:2000',
            'referral_type' => 'in:internal,external',
            'referral_status' => 'nullable|array',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:500',
            'comments' => 'nullable|string|max:1000',
            'interest_level' => 'in:very_low,low,medium,high,very_high',
            'follow_up_actions' => 'nullable|array',
        ]);

        $referralCard->update($request->only([
            'referral_date', 'referral_description', 'referral_type',
            'referral_status', 'contact_name', 'contact_phone',
            'contact_email', 'contact_address', 'comments',
            'interest_level', 'follow_up_actions'
        ]));

        return response()->json([
            'message' => 'Ficha de referencia actualizada exitosamente',
            'referral' => $referralCard->fresh()->load(['meeting', 'fromUser', 'toUser']),
        ]);
    }

    public function destroy(ReferralCard $referralCard)
    {
        $user = request()->user();
        
        if ($referralCard->from_user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el creador puede eliminar esta ficha'
            ], 403);
        }

        // Solo se puede eliminar si está en borrador
        if ($referralCard->status !== 'draft') {
            return response()->json([
                'message' => 'Solo se pueden eliminar fichas en borrador'
            ], 422);
        }

        $referralCard->delete();

        return response()->json([
            'message' => 'Ficha de referencia eliminada exitosamente',
        ]);
    }

    public function send(Request $request, ReferralCard $referralCard)
    {
        $user = $request->user();
        
        if ($referralCard->from_user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el creador puede enviar esta ficha'
            ], 403);
        }

        if ($referralCard->status !== 'draft') {
            return response()->json([
                'message' => 'Esta ficha ya fue enviada'
            ], 422);
        }

        $referralCard->send();

        return response()->json([
            'message' => 'Ficha de referencia enviada exitosamente',
            'referral' => $referralCard->fresh()->load(['meeting', 'fromUser', 'toUser']),
        ]);
    }

    public function markAsReceived(Request $request, ReferralCard $referralCard)
    {
        $user = $request->user();
        
        if ($referralCard->to_user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el destinatario puede marcar como recibida'
            ], 403);
        }

        if ($referralCard->status !== 'sent') {
            return response()->json([
                'message' => 'Esta ficha no está en estado enviada'
            ], 422);
        }

        $referralCard->markAsReceived();

        return response()->json([
            'message' => 'Ficha marcada como recibida',
            'referral' => $referralCard->fresh()->load(['meeting', 'fromUser', 'toUser']),
        ]);
    }

    public function complete(Request $request, ReferralCard $referralCard)
    {
        $user = $request->user();
        
        if ($referralCard->to_user_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el destinatario puede completar la ficha'
            ], 403);
        }

        if ($referralCard->status !== 'received') {
            return response()->json([
                'message' => 'Esta ficha debe estar recibida para completarla'
            ], 422);
        }

        $request->validate([
            'follow_up_actions' => 'nullable|array',
            'comments' => 'nullable|string|max:1000',
        ]);

        $updateData = ['status' => 'completed'];
        
        if ($request->has('follow_up_actions')) {
            $updateData['follow_up_actions'] = $request->follow_up_actions;
        }
        
        if ($request->has('comments')) {
            $updateData['comments'] = $request->comments;
        }

        $referralCard->update($updateData);

        return response()->json([
            'message' => 'Ficha completada exitosamente',
            'referral' => $referralCard->fresh()->load(['meeting', 'fromUser', 'toUser']),
        ]);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_sent' => $user->sentReferrals()->count(),
            'total_received' => $user->receivedReferrals()->count(),
            'pending_to_send' => $user->sentReferrals()->draft()->count(),
            'pending_to_receive' => $user->receivedReferrals()->sent()->count(),
            'completed_referrals' => $user->sentReferrals()->completed()->count() + 
                                   $user->receivedReferrals()->completed()->count(),
            'high_interest_referrals' => $user->sentReferrals()
                ->whereIn('interest_level', ['high', 'very_high'])
                ->count(),
            'this_month_referrals' => $user->sentReferrals()
                ->byDateRange(now()->startOfMonth(), now()->endOfMonth())
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function getByMeeting(Request $request, OneToOneMeeting $meeting)
    {
        $user = $request->user();
        
        if (!$meeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes acceso a esta reunión'
            ], 403);
        }

        $referrals = $meeting->referralCards()
                           ->with(['fromUser', 'toUser'])
                           ->orderBy('referral_date', 'desc')
                           ->get();

        return response()->json([
            'referrals' => $referrals,
            'meeting' => $meeting->load(['requester', 'requested']),
        ]);
    }
}