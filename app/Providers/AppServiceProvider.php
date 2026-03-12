<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // <--- WAJIB TAMBAH INI

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
   public function boot()
{
    // Paksa semua URL/Form pakai HTTPS kalau tidak di mode lokal
    if (config('app.env') !== 'local') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
}