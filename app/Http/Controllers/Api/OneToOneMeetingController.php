<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OneToOneMeeting;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OneToOneMeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OneToOneMeeting::with(['requester', 'requested', 'referralCards'])
                                ->forUser($user->id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            if ($request->type === 'upcoming') {
                $query->upcoming();
            } elseif ($request->type === 'past') {
                $query->past();
            }
        }

        // Filtro por rango de fechas
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->byDateRange($startDate, $endDate);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $meetings = $query->orderBy('meeting_date', 'desc')->paginate(20);

        // Agregar información adicional
        $meetings->getCollection()->transform(function ($meeting) use ($user) {
            $meeting->is_requester = $meeting->requester_id === $user->id;
            $meeting->other_participant = $meeting->getOtherParticipant($user);
            $meeting->can_modify = $meeting->canBeModifiedBy($user);
            return $meeting;
        });

        return response()->json($meetings);
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'meeting_date' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
            'meeting_type' => 'in:in_person,virtual,phone',
            'purpose' => 'required|string|max:1000',
            'agenda' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'contact_info' => 'nullable|array',
            'priority' => 'in:low,medium,high,urgent',
        ]);

        // Verificar que el usuario solicitado esté activo y sea miembro
        $requestedUser = User::findOrFail($request->requested_id);
        if (!$requestedUser->is_active || $requestedUser->membership_status !== 'active') {
            return response()->json([
                'message' => 'El usuario solicitado no está disponible para reuniones'
            ], 422);
        }

        // Verificar que no haya una reunión pendiente entre los mismos usuarios
        $existingMeeting = OneToOneMeeting::where(function ($query) use ($request) {
            $query->where('requester_id', $request->user()->id)
                  ->where('requested_id', $request->requested_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('requester_id', $request->requested_id)
                  ->where('requested_id', $request->user()->id);
        })->where('status', 'pending')->first();

        if ($existingMeeting) {
            return response()->json([
                'message' => 'Ya existe una solicitud de reunión pendiente con este usuario'
            ], 422);
        }

        $meeting = OneToOneMeeting::create([
            'requester_id' => $request->user()->id,
            'requested_id' => $request->requested_id,
            'meeting_date' => $request->meeting_date,
            'location' => $request->location,
            'meeting_type' => $request->meeting_type ?? 'in_person',
            'purpose' => $request->purpose,
            'agenda' => $request->agenda,
            'notes' => $request->notes,
            'contact_info' => $request->contact_info,
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json([
            'message' => 'Solicitud de reunión enviada exitosamente',
            'meeting' => $meeting->load(['requester', 'requested']),
        ], 201);
    }

    public function show(OneToOneMeeting $oneToOneMeeting)
    {
        $user = request()->user();
        
        // Verificar que el usuario tenga acceso a esta reunión
        if (!$oneToOneMeeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes acceso a esta reunión'
            ], 403);
        }

        $oneToOneMeeting->load(['requester', 'requested', 'referralCards.fromUser', 'referralCards.toUser']);
        
        return response()->json([
            'meeting' => $oneToOneMeeting,
            'is_requester' => $oneToOneMeeting->requester_id === $user->id,
            'other_participant' => $oneToOneMeeting->getOtherParticipant($user),
            'can_modify' => $oneToOneMeeting->canBeModifiedBy($user),
        ]);
    }

    public function update(Request $request, OneToOneMeeting $oneToOneMeeting)
    {
        $user = $request->user();
        
        if (!$oneToOneMeeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes permisos para modificar esta reunión'
            ], 403);
        }

        $request->validate([
            'meeting_date' => 'sometimes|date|after:now',
            'confirmed_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'meeting_type' => 'in:in_person,virtual,phone',
            'purpose' => 'sometimes|string|max:1000',
            'agenda' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'requester_notes' => 'nullable|string|max:1000',
            'requested_notes' => 'nullable|string|max:1000',
            'contact_info' => 'nullable|array',
            'priority' => 'in:low,medium,high,urgent',
        ]);

        $updateData = $request->only([
            'meeting_date', 'confirmed_date', 'location', 'meeting_type',
            'purpose', 'agenda', 'notes', 'contact_info', 'priority'
        ]);

        // Solo el solicitante puede actualizar sus notas
        if ($oneToOneMeeting->requester_id === $user->id && $request->has('requester_notes')) {
            $updateData['requester_notes'] = $request->requester_notes;
        }

        // Solo el solicitado puede actualizar sus notas
        if ($oneToOneMeeting->requested_id === $user->id && $request->has('requested_notes')) {
            $updateData['requested_notes'] = $request->requested_notes;
        }

        $oneToOneMeeting->update($updateData);

        return response()->json([
            'message' => 'Reunión actualizada exitosamente',
            'meeting' => $oneToOneMeeting->fresh()->load(['requester', 'requested']),
        ]);
    }

    public function destroy(OneToOneMeeting $oneToOneMeeting)
    {
        $user = request()->user();
        
        if (!$oneToOneMeeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta reunión'
            ], 403);
        }

        // Solo se puede eliminar si está pendiente
        if ($oneToOneMeeting->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden eliminar reuniones pendientes'
            ], 422);
        }

        $oneToOneMeeting->delete();

        return response()->json([
            'message' => 'Reunión eliminada exitosamente',
        ]);
    }

    public function accept(Request $request, OneToOneMeeting $oneToOneMeeting)
    {
        $user = $request->user();
        
        // Solo el usuario solicitado puede aceptar
        if ($oneToOneMeeting->requested_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el usuario solicitado puede aceptar la reunión'
            ], 403);
        }

        if ($oneToOneMeeting->status !== 'pending') {
            return response()->json([
                'message' => 'Esta reunión ya no está pendiente'
            ], 422);
        }

        $request->validate([
            'confirmed_date' => 'nullable|date|after:now',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oneToOneMeeting->accept();
        
        if ($request->has('confirmed_date')) {
            $oneToOneMeeting->update(['confirmed_date' => $request->confirmed_date]);
        }
        
        if ($request->has('location')) {
            $oneToOneMeeting->update(['location' => $request->location]);
        }
        
        if ($request->has('notes')) {
            $oneToOneMeeting->update(['requested_notes' => $request->notes]);
        }

        return response()->json([
            'message' => 'Reunión aceptada exitosamente',
            'meeting' => $oneToOneMeeting->fresh()->load(['requester', 'requested']),
        ]);
    }

    public function decline(Request $request, OneToOneMeeting $oneToOneMeeting)
    {
        $user = $request->user();
        
        if ($oneToOneMeeting->requested_id !== $user->id) {
            return response()->json([
                'message' => 'Solo el usuario solicitado puede rechazar la reunión'
            ], 403);
        }

        if ($oneToOneMeeting->status !== 'pending') {
            return response()->json([
                'message' => 'Esta reunión ya no está pendiente'
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $oneToOneMeeting->decline();
        
        if ($request->has('reason')) {
            $oneToOneMeeting->update(['requested_notes' => $request->reason]);
        }

        return response()->json([
            'message' => 'Reunión rechazada',
            'meeting' => $oneToOneMeeting->fresh()->load(['requester', 'requested']),
        ]);
    }

    public function complete(Request $request, OneToOneMeeting $oneToOneMeeting)
    {
        $user = $request->user();
        
        if (!$oneToOneMeeting->canBeModifiedBy($user)) {
            return response()->json([
                'message' => 'No tienes permisos para completar esta reunión'
            ], 403);
        }

        if ($oneToOneMeeting->status !== 'accepted') {
            return response()->json([
                'message' => 'Solo se pueden completar reuniones aceptadas'
            ], 422);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $oneToOneMeeting->complete();
        
        if ($request->has('notes')) {
            if ($oneToOneMeeting->requester_id === $user->id) {
                $oneToOneMeeting->update(['requester_notes' => $request->notes]);
            } else {
                $oneToOneMeeting->update(['requested_notes' => $request->notes]);
            }
        }

        return response()->json([
            'message' => 'Reunión marcada como completada',
            'meeting' => $oneToOneMeeting->fresh()->load(['requester', 'requested']),
        ]);
    }

    public function getStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_meetings' => $user->allMeetings()->count(),
            'pending_requests' => $user->receivedMeetingRequests()->pending()->count(),
            'sent_requests' => $user->requestedMeetings()->pending()->count(),
            'upcoming_meetings' => $user->allMeetings()->upcoming()->count(),
            'completed_meetings' => $user->allMeetings()->completed()->count(),
            'this_month_meetings' => $user->allMeetings()
                ->byDateRange(now()->startOfMonth(), now()->endOfMonth())
                ->count(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }
}