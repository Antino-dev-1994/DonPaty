<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\Inertia;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $permissions = $request->user()
            ? $request->user()->roles()->with('permissions:id,name')->get()->flatMap->permissions->pluck('name')->unique()->values()
            : collect();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $permissions,
            ],
            'flash' => [
                'toast' => fn () => $this->toast($request),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /** @return array{type: 'success'|'info'|'warning'|'error', message: string}|null */
    private function toast(Request $request): ?array
    {
        $toast = Inertia::getFlashed($request)['toast'] ?? null;
        if (is_array($toast) && isset($toast['type'], $toast['message'])) {
            return $toast;
        }

        foreach (['success', 'info', 'warning', 'error'] as $type) {
            $message = $request->session()->get($type);
            if (is_string($message) && $message !== '') {
                return ['type' => $type, 'message' => $message];
            }
        }

        return null;
    }
}
