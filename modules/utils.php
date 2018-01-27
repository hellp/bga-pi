<?php
// https://gist.github.com/sobi3ch/5451004
/**
 * Pluck an array of values from an array. (Only for PHP 5.3+)
 *
 * @param    $array - data
 * @param    $key - value you want to pluck from array
 * @param    $preserveKeys - (default: false)
 *
 * @return plucked array only with key data
 */
function array_pluck($array, $key, $preserveKeys=false) {
    $arr = array_map(function($v) use ($key) {
        return is_object($v) ? $v->$key : $v[$key];
    }, $array);
    if ($preserveKeys) return $arr;
    return array_values($arr);
}

function array_filter_by_keys($array, $keys) {
    $out = array();
    foreach ($keys as $key) {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            $out[$key] = $array[$key];
        }
    }
    return $out;
}

/**
 * Simple 2-dimensional flatten for arrays.
 */
function array_flatten($arr) {
    $result = call_user_func_array('array_merge', array_map('array_values', $arr));
    return $result;
}
