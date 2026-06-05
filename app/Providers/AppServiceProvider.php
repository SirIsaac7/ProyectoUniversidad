<?php

namespace App\Providers;

use App\Policies\InicioPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('view-inicio', [InicioPolicy::class, 'view']);

        Gate::before(function ($user, string $ability) {
            return $user->hasRole('SUPERADMIN') ? true : null;
        });

        Event::listen(Login::class, function (Login $event){
            activity('autenticacion')
                ->causedBy($event->user)
                ->performedOn($event->user)
                ->withProperties([
                    'guard' => $event->guard,
                    'request' => [
                        'ip' => request()->ip(),
                        'url' => request()->fullUrl(),
                        'user_agent' => request()->userAgent(),
                    ],
                ])
                ->event('login')
                ->log('Inicio de sesion');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                activity('autenticacion')
                    ->causedBy($event->user)
                    ->performedOn($event->user)
                    ->withProperties([
                        'guard' => $event->guard,
                        'request' => [
                            'ip' => request()->ip(),
                            'url' => request()->fullUrl(),
                            'user_agent' => request()->userAgent(),
                        ],
                    ])
                    ->event('logout')
                    ->log('Cierre de sesion');
            }
        });
    }
}
