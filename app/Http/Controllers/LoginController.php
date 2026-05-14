<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && $user->activo && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));

            return redirect()->intended($this->rutaInicial($user));
        }

        return back()->withErrors([
            'username' => 'Las credenciales no son válidas.',
        ])->onlyInput('username');
    }

    /**
     * Decide a qué panel mandar al usuario tras el login según su acceso.
     *  - Si tiene acceso web (incluido "ambos"): panel web (/).
     *  - Si solo tiene acceso móvil: panel móvil (/m).
     *  - Si no tiene acceso a ninguno (caso raro): vuelve al login con mensaje.
     */
    private function rutaInicial(User $user): string
    {
        if ($user->tieneAccesoWeb()) {
            return '/';
        }

        if ($user->tieneAccesoMovil()) {
            return '/m';
        }

        return '/login';
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
