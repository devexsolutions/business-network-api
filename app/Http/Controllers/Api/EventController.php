<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['user.company', 'company', 'attendees'])
            ->published();

        // Filtros
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('upcoming') && $request->upcoming) {
            $query->upcoming();
        }

        if ($request->has('location')) {
            $query->whereJsonContains('location->city', $request->location);
        }

        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        $events = $query->latest('start_date')->paginate(10);

        // Agregar información de asistencia del usuario actual
        $events->getCollection()->transform(function ($event) use ($request) {
            $event->user_is_attending = $request->user() 
                ? $event->attendees()->where('user_id', $request->user()->id)->exists()
                : false;
            $event->attendees_count = $event->attendees()->where('status', 'registered')->count();
            return $event;
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'in:networking,conference,workshop,webinar',
            'format' => 'in:in_person,virtual,hybrid',
            'location' => 'nullable|array',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'max_attendees' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'requires_approval' => 'boolean',
        ]);

        $event = $request->user()->events()->create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type ?? 'networking',
            'format' => $request->format ?? 'in_person',
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_attendees' => $request->max_attendees,
            'price' => $request->price ?? 0,
            'tags' => $request->tags,
            'requires_approval' => $request->requires_approval ?? false,
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json([
            'message' => 'Evento creado exitosamente',
            'event' => $event->load(['user.company', 'company']),
        ], 201);
    }

    public function show(Event $event)
    {
        return response()->json([
            'event' => $event->load([
                'user.company', 
                'company', 
                'attendeeUsers' => function ($query) {
                    $query->where('event_attendees.status', 'registered');
                }
            ]),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'in:networking,conference,workshop,webinar',
            'format' => 'in:in_person,virtual,hybrid',
            'location' => 'nullable|array',
            'start_date' => 'sometimes|date|after:now',
            'end_date' => 'sometimes|date|after:start_date',
            'max_attendees' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'requires_approval' => 'boolean',
        ]);

        $event->update($request->only([
            'title', 'description', 'type', 'format', 'location',
            'start_date', 'end_date', 'max_attendees', 'price',
            'tags', 'requires_approval'
        ]));

        return response()->json([
            'message' => 'Evento actualizado exitosamente',
            'event' => $event->load(['user.company', 'company']),
        ]);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json([
            'message' => 'Evento eliminado exitosamente',
        ]);
    }

    public function attend(Request $request, Event $event)
    {
        $user = $request->user();

        // Verificar si ya está registrado
        $existingAttendance = EventAttendee::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existingAttendance) {
            if ($existingAttendance->status === 'cancelled') {
                $existingAttendance->update(['status' => 'registered']);
                $message = 'Te has registrado nuevamente al evento';
            } else {
                return response()->json([
                    'message' => 'Ya estás registrado en este evento',
                ], 422);
            }
        } else {
            // Verificar disponibilidad
            if (!$event->hasAvailableSpots()) {
                return response()->json([
                    'message' => 'El evento ha alcanzado su capacidad máxima',
                ], 422);
            }

            EventAttendee::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => 'registered',
            ]);
            $message = 'Te has registrado exitosamente al evento';
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    public function unattend(Request $request, Event $event)
    {
        $user = $request->user();

        $attendance = EventAttendee::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No estás registrado en este evento',
            ], 422);
        }

        $attendance->cancel();

        return response()->json([
            'message' => 'Has cancelado tu asistencia al evento',
        ]);
    }
}