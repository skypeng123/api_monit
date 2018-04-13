<?php

$client = new swoole_client(SWOOLE_SOCK_UDP);
$client->connect('127.0.0.1', 9505, 1);

while ($i < 1000) {
    $client->send($i . "\n");
    $message = $client->recv();
    echo "Get Message From Server:{$message}\n";
    $i++;
}