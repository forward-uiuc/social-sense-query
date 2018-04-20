<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\Query\QueryHistory;
use App\Observers\QueryHistoryObserver;
use App\Models\Query\Query;
use App\Models\MetaQuery\MetaQueryFunction;

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
				'function' => MetaQueryFunction::class
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
