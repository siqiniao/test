<?php
include 'fns.php';
error_reporting(-1);


while (1) {
    $m = new Memcached();
    $m->addServer('127.0.0.1', 11211);
    /* 10秒内清除所有元素 */
    $m->flush(0);

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->flushAll();
    
    usleep(100000);
    
}

