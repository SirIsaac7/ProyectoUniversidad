<?php

namespace App\Http\Controllers;

use App\Services\CelularVerificacionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index');
    }

    public function editLocalPassword(Request $request)
    {
        abort_unless($request->user()?->google_id, 403);

        return view('perfil.password-local');
    }

    public function updateLocalPassword(Request $request)
    {
        abort_unless($request->user()?->google_id, 403);

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()
            ->route('perfil.index')
            ->with('status', 'local-password-updated');
    }

    public function enviarCodigoCelular(Request $request, CelularVerificacionService $celularVerificacionService)
    {
        $usuario = $request->user();

        abort_unless($usuario && filled($usuario->celular), 422);

        $enviado = $celularVerificacionService->enviarCodigo($usuario);

        return redirect()
            ->route('perfil.index')
            ->with('status', $enviado ? 'cell-code-sent' : 'cell-code-failed');
    }

    public function verificarCelular(Request $request, CelularVerificacionService $celularVerificacionService)
    {
        $request->validate([
            'codigo' => ['required', 'digits:6'],
        ]);

        $verificado = $celularVerificacionService->verificar(
            $request->user(),
            $request->input('codigo')
        );

        return redirect()
            ->route('perfil.index')
            ->with('status', $verificado ? 'cell-verified' : 'cell-code-invalid');
    }
}
