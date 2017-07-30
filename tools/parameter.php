<?php
require_once '../fns.php';

global $argv;

if (!isset($argv[1])) {
    ee("no params");
    exit();
}

ee(unserialize(base64_decode($argv[1])));

