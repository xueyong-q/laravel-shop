<?php

use Illuminate\Support\Facades\Route;

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

/**
 * 设置浮点数的精确小数点
 *
 * @param string $value
 * @param integer $scale
 * @return void
 */
function format_number(string $value, int $scale = 2)
{
    return number_format($value, $scale, '.', '');
}
