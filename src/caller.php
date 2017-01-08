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
$channel->queue_declare( 'commands' );

# Create and send the message
$message = new AMQPMessage( json_encode( [ 'timestamp' => date( 'c' ) ], JSON_PRETTY_PRINT ) );
$channel->basic_publish( $message, '', 'commands' );

echo " [x] Message sent.\n";

# Close channel and connection
$channel->close();
$connection->close();
