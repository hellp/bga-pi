<?php
// https://gist.github.com/sobi3ch/5451004
/**
 * Pluck an array of values from an array. (Only for PHP 5.3+)
 *
 * @param  $array - data
 * @param  $key - value you want to pluck from array
 *
 * @return plucked array only with key data
 */
function array_pluck($array, $key) {
  return array_map(function($v) use ($key)	{
    return is_object($v) ? $v->$key : $v[$key];
  }, $array);
}

function calcX($x, $deg, $len) {
  return $x + cos(deg2rad($deg)) * $len;
}

function calcY($y, $deg, $len) {
  return $y + sin(deg2rad($deg)) * $len;
}
