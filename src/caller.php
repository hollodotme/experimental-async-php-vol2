<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require(__DIR__ . '/../vendor/autoload.php');

$redis = new \Redis();
$redis->connect( 'localhost', 6379, 2.0 );

$message = [
	'timestamp' => date( 'c' ),
];

$redis->publish( 'commands', json_encode( $message, JSON_PRETTY_PRINT ) );
