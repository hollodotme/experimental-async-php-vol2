<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require(__DIR__ . '/../vendor/autoload.php');

$connection = new AMQPStreamConnection( 'localhost', 5672, 'guest', 'guest' );
$channel    = $connection->channel();

$channel->queue_declare( 'commands' );

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function ( AMQPMessage $msg )
{
	var_dump( $msg );
	echo " [x] Received ", $msg->getBody(), "\n";
};

$channel->basic_consume( 'commands', '', false, true, false, false, $callback );

while ( count( $channel->callbacks ) )
{
	$channel->wait();
}
