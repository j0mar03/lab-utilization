<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureRole Middleware
 *
 * Restricts route access to users with one or more allowed roles.
 *
 * Usage in routes/web.php:
 *
 *   // Single role:
 *   Route::middleware(['auth', 'role:lab_head'])->group(function () { ... });
 *
 *   // Multiple allowed roles (pipe-separated):
 *   Route::middleware(['auth', 'role:lab_head|student_assistant'])->group(function () { ... });
 *
 * Register the alias in bootstrap/app.php:
 *   $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Not logged in at all — redirect to login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRole      = $request->user()->role;
        $allowedRoles  = [];

        // Support both pipe-separated single-arg "role:lab_head|faculty"
        // and variadic "role:lab_head,faculty" calling conventions
        foreach ($roles as $role) {
            foreach (explode('|', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            // Authenticated but wrong role — show 403, not redirect to login
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
