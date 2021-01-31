<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

const HOST = 'localhost';
const PORT = 5672;
const USER = 'guest';
const PASS = 'guest';

return (new AMQPStreamConnection(HOST, PORT, USER, PASS))->channel();