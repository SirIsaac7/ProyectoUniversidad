<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function recientes(Request $request): JsonResponse
    {
        $notificaciones = $request->user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($notificacion) => [
                'id' => $notificacion->id,
                'titulo' => $notificacion->data['titulo'] ?? 'Notificacion',
                'mensaje' => $notificacion->data['mensaje'] ?? '',
                'tipo' => $notificacion->data['tipo'] ?? 'info',
                'url' => $notificacion->data['url'] ?? null,
                'leida' => $notificacion->read_at !== null,
                'fecha' => $notificacion->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'total_no_leidas' => $request->user()->unreadNotifications()->count(),
            'notificaciones' => $notificaciones,
        ]);
    }

    public function marcarLeida(Request $request, string $id): JsonResponse
    {
        $notificacion = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notificacion->markAsRead();

        return response()->json([
            'mensaje' => 'Notificacion marcada como leida.',
            'total_no_leidas' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'mensaje' => 'Todas las notificaciones fueron marcadas como leidas.',
            'total_no_leidas' => 0,
        ]);
    }
}
