<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

# Connect and retrieve a channel
$connection = new AMQPStreamConnection( 'localhost', 5672, 'guest', 'guest' );
$channel    = $connection->channel();

# Make sure the queue 'commands' exist
# Make the queue persistent (set 3rd parameter to true)
$channel->queue_declare( 'commands', false, true );

$payload = json_encode( [ 'number' => $argv[1] ], JSON_PRETTY_PRINT );

# Create and send the message
$message = new AMQPMessage(
	$payload,
	[
		# Make message persistent
		'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
	]
);

$channel->basic_publish( $message, '', 'commands' );

echo " [x] Message sent: {$argv[1]}\n";

# Close channel and connection
$channel->close();
$connection->close();
