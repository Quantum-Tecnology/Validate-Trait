<?php

declare(strict_types = 1);

namespace App\Providers;

use App\Rules\MaxDays;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class RulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar a regra personalizada
        Validator::extend('max_days', function ($attribute, $value, $parameters, $validator) {
            $compareColumn = $parameters[0] ?? null;
            $maxDays = (int) ($parameters[1] ?? 0);

            if (!$compareColumn || !$maxDays) {
                return false;
            }

            $rule = new MaxDays($compareColumn, $maxDays);

            return $rule->validate($attribute, $value, function ($message) use ($validator) {
                $validator->errors()->add($attribute, $message);
            });
        });
    }
}
