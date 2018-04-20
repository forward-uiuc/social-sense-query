<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Factories\RunFactory;

class RunFactoryProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
			$this->app->singleton(RunFactory::class, function($app) {
				return new RunFactory();
			});
    }
}
