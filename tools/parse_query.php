<?php
require_once '../fns.php';

global $argv;

if (!isset($argv[1])) {
    ee("no params");
    exit();
}

parse_str($argv[1], $data);


$keys = [
    'user_id',
    'timestamp',
    'sign',
    'bookId',
    'source',
    'author',
    'bookName',
    
];

$keys = [
    'user_id',
    'timestamp',
    'sign',
    'bookIds',
    
];

foreach ($data as $key=>$val)
{
//     if($keys && !in_array($key, $keys))
//     {
//         continue;
//     }
    
    echo "{$key}:{$val}\n";
    
}


