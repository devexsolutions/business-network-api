<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $connections = $user->connections()
            ->with('company')
            ->paginate(20);

        return response()->json($connections);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($request->user_id);

        // Verificar que no sea el mismo usuario
        if ($user->id === $targetUser->id) {
            return response()->json([
                'message' => 'No puedes conectarte contigo mismo',
            ], 422);
        }

        // Verificar si ya existe una conexión
        $existingConnection = Connection::where(function ($query) use ($user, $targetUser) {
            $query->where('requester_id', $user->id)
                  ->where('addressee_id', $targetUser->id);
        })->orWhere(function ($query) use ($user, $targetUser) {
            $query->where('requester_id', $targetUser->id)
                  ->where('addressee_id', $user->id);
        })->first();

        if ($existingConnection) {
            return response()->json([
                'message' => 'Ya existe una solicitud de conexión con este usuario',
            ], 422);
        }

        $connection = Connection::create([
            'requester_id' => $user->id,
            'addressee_id' => $targetUser->id,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Solicitud de conexión enviada exitosamente',
            'connection' => $connection->load(['requester', 'addressee']),
        ], 201);
    }

    public function show(Connection $connection)
    {
        return response()->json([
            'connection' => $connection->load(['requester', 'addressee']),
        ]);
    }

    public function update(Request $request, Connection $connection)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        // Solo el destinatario puede actualizar el estado
        if ($connection->addressee_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar esta conexión',
            ], 403);
        }

        if ($request->status === 'accepted') {
            $connection->accept();
        } else {
            $connection->decline();
        }

        return response()->json([
            'message' => 'Conexión actualizada exitosamente',
            'connection' => $connection->fresh()->load(['requester', 'addressee']),
        ]);
    }

    public function destroy(Connection $connection)
    {
        $user = request()->user();
        
        // Solo el solicitante o destinatario pueden eliminar la conexión
        if ($connection->requester_id !== $user->id && $connection->addressee_id !== $user->id) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta conexión',
            ], 403);
        }

        $connection->delete();

        return response()->json([
            'message' => 'Conexión eliminada exitosamente',
        ]);
    }

    public function pending(Request $request)
    {
        $user = $request->user();
        
        $pendingConnections = Connection::with(['requester.company'])
            ->where('addressee_id', $user->id)
            ->pending()
            ->latest()
            ->paginate(10);

        return response()->json($pendingConnections);
    }

    public function sent(Request $request)
    {
        $user = $request->user();
        
        $sentConnections = Connection::with(['addressee.company'])
            ->where('requester_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json($sentConnections);
    }
}