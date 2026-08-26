<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn truy cập nếu người dùng không thuộc một trong các vai trò cho phép.
 *
 * Quy ước vai trò dùng chung với app/Policies/*: 1 = admin, 0 = nhân viên.
 * Dùng: Route::middleware('role:1')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Bạn cần đăng nhập.');
        }

        if (!in_array((string) $user->role, $roles, true)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}
