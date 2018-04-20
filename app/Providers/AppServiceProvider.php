<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Query\QueryHistory;
use App\Observers\QueryHistoryObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Query\Query;

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
			Relation::morphMap([
				'query' => Query::class,
				'query_value' => QueryHistory::class
			]);
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
