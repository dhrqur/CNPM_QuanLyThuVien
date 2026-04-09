<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($roles !== [] && !in_array($user->vaitro, $roles, true)) {
            return redirect()
                ->route('dashboard')
                ->with('danger', 'Bạn không có quyền thực hiện chức năng này.');
        }

        return $next($request);
    }
}
