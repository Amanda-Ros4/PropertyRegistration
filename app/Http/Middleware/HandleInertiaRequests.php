<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Laravel\Fortify\Features;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'canRegister' => fn () => Route::has('register'),
            'canResetPassword' => fn () => Features::enabled(Features::resetPasswords())
                && Route::has('password.request'),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'locale' => app()->getLocale(),
            'flash' => fn () => [
                'type' => $request->session()->get('flash.type'),
                'message' => $request->session()->get('flash.message'),
            ],
        ];
    }
}
