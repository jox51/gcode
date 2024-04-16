<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckSubcriptionStatus {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Check if the user is authenticated and has a valid subscription or their email is in the manual user list.
        if ($this->userHasAccess($request)) {
            return $next($request);
        } else {
            return redirect()->route('welcome');
        }
    }

    /**
     * Check if the user has access based on subscription status or manual override.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function userHasAccess(Request $request): bool {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Check if the user's subscription is active or if their email is in the list of manually allowed emails.
        return $user->subscription_status || in_array($user->email, $this->manualUserList());
    }

    /**
     * A list of users who can manually access the site without a subscription.
     *
     * @return array
     */
    protected function manualUserList(): array {
        return ['test2@test.com', 'test@test.com'];
    }
}
