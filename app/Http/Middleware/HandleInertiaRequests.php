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
    public function version(Request $request): string|null
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
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'notifications' => $request->user() ? collect($request->user()->unreadNotifications()->take(10)->get())->map(function($notif) {
                    return [
                        'id' => $notif->id,
                        'data' => $notif->data,
                        'created_at' => $notif->created_at->diffForHumans(),
                    ];
                }) : [],
                'unread_notifications_count' => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new \Tighten\Ziggy\Ziggy())->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}