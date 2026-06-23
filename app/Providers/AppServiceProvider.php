<?php

namespace App\Providers;

use App\Policies\InicioPolicy;
use App\Support\Backup\BackupTemporaryDirectory;
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
        $this->app->bind('backup-temporary-project', function () {
            return new BackupTemporaryDirectory(
                config('backup.backup.temporary_directory') ?? storage_path('app/backup-temp')
            );
        });
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
