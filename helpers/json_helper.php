<?php defined('BASEPATH') OR exit('No direct script access allowed');


function my_json_encode($t){
//    json_encode(array_map(function($t){ return is_string($t) ? utf8_encode($t) : $t; }, $array));
    $func = create_function('$t', 'return is_string($t) ? utf8_encode($t) : $t;');
    return json_encode(array_walk_recursive($t,$func));
}
function my_json_decode($array){
    return json_decode(array_walk_recursive($array,function($t){ return is_string($t) ? utf8_decode($t) : $t; }));
}