<?php

declare(strict_types = 1);

namespace QuantumTecnology\ValidateTrait\Provider;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\ValidateTrait\Rules\MaxDays;

class RulesProvider extends ServiceProvider
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
            $maxDays       = (int) ($parameters[1] ?? config('rules.max_days', 30));

            if (!$compareColumn || !$maxDays) {
                return false;
            }

            $rule = new MaxDays($compareColumn, $maxDays);

            try {
                $rule->validate($attribute, $value, function ($message) use ($validator, $attribute) {
                    $validator->errors()->add($attribute, $message);
                });

                return true; // Validação passou
            } catch (Exception $e) {
                return false; // Validação falhou
            }
        });
    }
}
