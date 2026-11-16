<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    // Only log state-changing methods
    private const LOGGED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!in_array($request->method(), self::LOGGED_METHODS)) {
            return $response;
        }

        $user = $request->user();
        if (!$user) {
            return $response;
        }

        // Skip if the response is a redirect back to the same page (validation failure)
        if ($response->isRedirect() && str_contains($response->headers->get('Location', ''), 'back')) {
            return $response;
        }

        try {
            $segments = $request->segments(); // e.g. ['admin', 'courses', '5']
            $action   = strtolower($request->method()) . ':' . implode('/', $segments);

            ActivityLog::create([
                'user_id'    => $user->id,
                'action'     => $action,
                'model_type' => $this->inferModel($segments),
                'model_id'   => $this->inferModelId($segments),
                'ip_address' => $request->ip(),
                'data'       => $this->safePayload($request),
            ]);
        } catch (\Throwable) {
            // Never break the request because of a logging failure
        }

        return $response;
    }

    private function inferModel(array $segments): ?string
    {
        // e.g. ['admin', 'courses', '5'] → 'courses'
        foreach ($segments as $i => $seg) {
            if (is_numeric($seg) && $i > 0) {
                return $segments[$i - 1];
            }
        }
        return $segments[array_key_last($segments)] ?? null;
    }

    private function inferModelId(array $segments): ?int
    {
        foreach ($segments as $seg) {
            if (is_numeric($seg)) {
                return (int) $seg;
            }
        }
        return null;
    }

    private function safePayload(Request $request): array
    {
        $skip = ['password', 'password_confirmation', 'current_password', '_token', '_method'];
        return collect($request->except($skip))
            ->map(fn ($v) => is_string($v) ? substr($v, 0, 500) : $v)
            ->filter(fn ($v) => !is_array($v) || count($v) < 20) // skip big arrays
            ->toArray();
    }
}
