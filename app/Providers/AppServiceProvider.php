<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Query\QueryHistory;
use App\Observers\QueryHistoryObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
			QueryHistory::observe(QueryHistoryObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
