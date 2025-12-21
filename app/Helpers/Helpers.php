<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

if (!function_exists('formatNumber')) {
    function formatNumber(?string $value): int|float|null
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $num = (float) $value;

        if ((int) $num == $num) {
            return (int) $num;
        }

        return round($num, 1);
    }
}
