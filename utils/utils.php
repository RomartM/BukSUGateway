<?php

function _gw_remove_http($url) {
    $disallowed = array('http://', 'https://');
    foreach($disallowed as $d) {
        if(strpos($url, $d) === 0) {
            return explode("/", str_replace($d, '', $url))[0];
        }
    }
    return $url;
}

function _gw_parse_url($url){
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    return $query;
}

function _gw_format_date($string_date){
    $date = date_create($string_date);
    return date_format($date, "n/j/Y");
}
add_filter('gw_format_date', '_gw_format_date');