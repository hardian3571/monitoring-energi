<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;
use Carbon\Carbon;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // LOGIKA LOGIN DITARUH DISINI (DI DALAM BOOT)
        Event::listen(Login::class, function ($event) {
            LoginHistory::create([
                'user_id' => $event->user->id,
                'name' => $event->user->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => Carbon::now(),
            ]);
        });
    }
}