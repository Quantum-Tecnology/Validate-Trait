<?php

declare(strict_types = 1);

/*
 * Helpers.
 */

if (!function_exists('data')) {
    function data(object | array | string $data = [], string $blank = ''): mixed
    {
        if (is_string($data)) {
            return request()->data()->{$data} ?? $blank;
        }

        return request()->data()->merge($data);
    }
}
