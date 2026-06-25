<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\Services\CelularVerificacionService;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $usuario = $request->user();
        $archivo = $request->file('avatar');
        $carpeta = public_path('uploads/usuarios');

        if (! File::exists($carpeta)) {
            File::makeDirectory($carpeta, 0755, true);
        }

        if (
            $usuario->avatar
            && ! str_starts_with($usuario->avatar, 'http://')
            && ! str_starts_with($usuario->avatar, 'https://')
            && File::exists(public_path($usuario->avatar))
        ) {
            File::delete(public_path($usuario->avatar));
        }

        $nombreArchivo = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($carpeta, $nombreArchivo);

        $usuario->update([
            'avatar' => 'uploads/usuarios/' . $nombreArchivo,
        ]);

        return redirect()
            ->route('perfil.index')
            ->with('status', 'avatar-updated');
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
