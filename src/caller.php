<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection( 'localhost', 5672, 'guest', 'guest' );
$channel    = $connection->channel();

$channel->queue_declare( 'commands' );

$message = new AMQPMessage( 'Do something' );
$channel->basic_publish( $message, '', 'commands' );

echo " [x] Sent 'Do something' to 'commands'\n";

$channel->close();
$connection->close();
