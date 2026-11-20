<?php

namespace App\Http\Controllers\Notificaciones;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notificaciones\IndexNotificacionRequest;
use App\Http\Requests\Notificaciones\UpdateNotificacionesRequest;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function __construct(
        protected NotificacionService $notificacionService
    ) {
        $this->middleware('permission:ver mis notificaciones')->only(['index', 'recientes']);
        $this->middleware('permission:gestionar mis notificaciones')->only([
            'marcarLeida',
            'marcarNoLeida',
            'marcarTodasLeidas',
            'accionMasiva',
        ]);
        $this->middleware('permission:eliminar mis notificaciones')->only('destroy');
    }

    public function index(IndexNotificacionRequest $request): View
    {
        $filtros = $request->validated();

        return view('notificaciones.index', [
            ...$this->notificacionService->centro($request->user(), $filtros),
            'filtros' => $filtros,
        ]);
    }

    public function recientes(Request $request): JsonResponse
    {
        $notificaciones = $this->notificacionService
            ->propiasRecientes($request->user())
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

    public function marcarLeida(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $this->notificacionService->marcarLeida($request->user(), $id);

        return $this->respuestaOperacion(
            $request,
            'Notificacion marcada como leida.',
            $request->user()->unreadNotifications()->count()
        );
    }

    public function marcarNoLeida(Request $request, string $id): RedirectResponse
    {
        $this->notificacionService->marcarNoLeida($request->user(), $id);

        return back()->with('success', 'Notificacion marcada como no leida.');
    }

    public function marcarTodasLeidas(Request $request): JsonResponse|RedirectResponse
    {
        $this->notificacionService->marcarTodasLeidas($request->user());

        return $this->respuestaOperacion($request, 'Todas las notificaciones fueron marcadas como leidas.', 0);
    }

    public function accionMasiva(UpdateNotificacionesRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $afectadas = $this->notificacionService->ejecutarAccionMasiva(
            $request->user(),
            $data['notificaciones'],
            $data['accion']
        );

        $accion = match ($data['accion']) {
            'leer' => 'marcadas como leidas',
            'no_leer' => 'marcadas como no leidas',
            'eliminar' => 'eliminadas',
        };

        return back()->with('success', "{$afectadas} notificaciones fueron {$accion}.");
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $this->notificacionService->eliminar($request->user(), $id);

        return back()->with('success', 'Notificacion eliminada correctamente.');
    }

    private function respuestaOperacion(
        Request $request,
        string $mensaje,
        int $totalNoLeidas
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'mensaje' => $mensaje,
                'total_no_leidas' => $totalNoLeidas,
            ]);
        }

        return back()->with('success', $mensaje);
    }
}
