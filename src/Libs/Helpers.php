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
            if (str_contains($data, '.')) {
                $parts = explode('.', $data);
                $value = $currentData;

                foreach ($parts as $part) {
                    if (is_array($value)) {
                        $value = $value[$part] ?? $blank;
                    } elseif (is_object($value)) {
                        $value = $value->{$part} ?? $blank;
                    } else {
                        return $blank;
                    }
                }

                return $value;
            }

            // Acesso simples (sem ponto)
            if (is_array($currentData)) {
                return $currentData[$data] ?? $blank;
            } elseif (is_object($currentData)) {
                return $currentData->{$data} ?? $blank;
            }
            return $blank;
        }

        if (is_array($data) || is_object($data)) {
            $currentData->merge($data);
        }

        return $currentData;
    }
}
