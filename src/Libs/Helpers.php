<?php

declare(strict_types = 1);

use QuantumTecnology\ValidateTrait\Data;

/*
 * Helpers.
 */

if (!function_exists('data')) {
    function data(object | array | string $data = [], mixed $blank = ''): mixed
    {
        static $currentData = new Data();

        if (is_string($data)) {
            return $currentData->{$data} ?? $blank;
        }

        if (is_array($data) || is_object($data)) {
            $currentData->merge($data);
        }

        return $currentData;
    }
}
