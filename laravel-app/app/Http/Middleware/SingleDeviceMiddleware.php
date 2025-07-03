<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SingleDeviceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar si el usuario está activo
            if (!$user->active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')->withErrors([
                    'account' => '🚫 Tu cuenta ha sido desactivada. Contacta al administrador.',
                ]);
            }
            
            $currentUserAgent = $request->header('User-Agent');
            
            // Si el usuario no tiene user_agent guardado, guardarlo ahora
            if (empty($user->user_agent)) {
                $user->update(['user_agent' => $currentUserAgent]);
                return $next($request);
            }
            
            // Comparar user agents
            if ($user->user_agent !== $currentUserAgent) {
                // Cerrar sesión del usuario actual
                Auth::logout();
                
                // Invalidar la sesión
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirigir al login con mensaje de error
                return redirect()->route('login')->withErrors([
                    'device' => '🚫 Tu cuenta ya está activa en otro dispositivo. Solo puedes usar una sesión a la vez.',
                ]);
            }
        }

        return $next($request);
    }
}