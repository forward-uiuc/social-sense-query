<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \App\Services\GQLServerService;

class GQLServerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services, called after all services providers have been registered
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
			$this->app->singleton(GQLServerService::class, function($app) {
				return new GQLServerService(config('services.graphql.server_uri'));
			});
    }
}
