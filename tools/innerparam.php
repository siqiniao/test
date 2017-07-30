<?php
require_once '../fns.php';

global $argv;

if (!isset($argv[1])) {
    ee("no params");
    exit();
}


parse_str($argv[1], $data);


$data['parameter'] = unserialize(base64_decode($data['parameter']));
$data['extra'] = json_decode($data['extra'], true);


ee($data);

