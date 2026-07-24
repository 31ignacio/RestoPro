<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActifMiddleware {
    public function handle(Request $request, Closure $next): mixed {
        if (Auth::check() && !Auth::user()->actif) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé.']);
        }
        return $next($request);
    }
}