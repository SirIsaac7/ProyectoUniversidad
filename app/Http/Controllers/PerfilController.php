<?php

namespace App\Http\Controllers;

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
}
