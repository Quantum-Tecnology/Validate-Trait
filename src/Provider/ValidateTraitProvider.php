<?php

namespace QuantumTecnology\ValidateTrait\Provider;

use QuantumTecnology\ValidateTrait\Data;
use QuantumTecnology\ValidateTrait\Provider\RuleServiceProvider;
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
        $this->app->register(RuleServiceProvider::class);

       $this->publishes([
            __DIR__.'/../config/hashids.php' => config_path('hashids.php'),
            __DIR__.'/../config/rules.php' => config_path('rules.php'),
        ], 'config');

        Request::macro('data', function ($key = null, $value = null) {
            if (is_null($key)) {
                return $this->input('data')['validated'] ?? app(Data::class);
            }

            if (is_null($value)) {
                return $this->input('data')[$key] ?? app(Data::class);
            }

            $data       = $this->input('data', []);
            $data[$key] = $value;
            $this->merge(['data' => $data]);
        });
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
