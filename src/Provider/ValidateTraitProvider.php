<?php

namespace QuantumTecnology\ValidateTrait\Provider;

use QuantumTecnology\ValidateTrait\Data;
use QuantumTecnology\ValidateTrait\Provider\RulesProvider;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ValidateTraitProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RulesProvider::class);

       $this->publishes([
            __DIR__.'/../config/hashids.php' => config_path('hashids.php'),
            __DIR__.'/../config/rules.php' => config_path('rules.php'),
        ], 'config');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
