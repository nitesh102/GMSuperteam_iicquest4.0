<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn () => array_merge(
                (new \Tighten\Ziggy\Ziggy)->toArray(),
                [
                    'url'      => rtrim($request->root(), '/'),
                    'location' => $request->url(),
                ]
            ),
            'locale' => app()->getLocale(),
            'availableLocales' => [
                ['code' => 'en', 'label' => 'English'],
                ['code' => 'ne', 'label' => 'नेपाली'],
            ],
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()?->getRoleNames(),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
            ],
        ];
    }
}
