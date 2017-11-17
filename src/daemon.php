<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require __DIR__ . '/../vendor/autoload.php';

# Connect to the same RabbitMP instance and get a channel
$connection = new AMQPStreamConnection( 'localhost', 5672, 'guest', 'guest' );
$channel    = $connection->channel();

# Make sure the queue "commands" exists
# Make the queue persistent (set 3rd parameter to true)
$channel->queue_declare( 'commands', false, true );

# Prepare the Fast CGI Client
$unixDomainSocket = new UnixDomainSocket( '/var/run/php/php7.1-fpm-commands.sock' );
$fpmClient        = new Client( $unixDomainSocket );

$daemonId = sprintf( 'D-%03d', random_int( 1, 100 ) );

# Define a callback function that is invoked whenever a message is consumed
$callback = function ( AMQPMessage $message ) use ( $fpmClient, $daemonId )
{
	# Decode the json message and encode it for sending to php-fpm
	$messageArray             = json_decode( $message->getBody(), true );
	$messageArray['daemonId'] = $daemonId;
	$body                     = http_build_query( $messageArray );

	# Send an async request to php-fpm pool and receive a request ID
	$request = new PostRequest( '/vagrant/src/worker.php', $body );

	# Add a response callback to acknowledge the message based on the worker result
	$request->addResponseCallbacks(
		function ( ProvidesResponseData $response ) use ( $message, $messageArray )
		{
			# In both cases (SUCCEEDED & FAILED) we want the message to be removed from the queue
			if ( \in_array( $response->getBody(), ['SUCCEEDED', 'FAILED'], true ) )
			{
				# Send the ACK(nowledgement) back to the channel for this particular message
				$message->get( 'channel' )->basic_ack( $message->get( 'delivery_tag' ) );

				echo " [√] Acknowledged process with ID {$response->getRequestId()} for message number {$messageArray['number']}\n";

				return;
			}

			# In case of REJECTED we want the message to be redistributed

			/**
			 * Send a N(egative)ACK(nowledgement) back to the channel for this particular message
			 * @see https://github.com/php-amqplib/php-amqplib/blob/master/demo/basic_nack.php
			 */
			$message->get( 'channel' )->basic_nack( $message->get( 'delivery_tag' ) );

			echo " [<-] Nacked process with ID {$response->getRequestId()} for message number {$messageArray['number']}\n";
		}
	);

	$requestId = $fpmClient->sendAsyncRequest( $request );

	echo " [x] Spawned process with ID {$requestId} for message number {$messageArray['number']}\n";
};

# Set the prefetch count to 1 for this consumer
$channel->basic_qos( null, 50, null );

# Request consumption for queue "commands" using the defined callback function
# Enable message acknowledgement (set 4th parameter to false)
$channel->basic_consume( 'commands', '', false, false, false, false, $callback );

# Wait to finish execution as long as the channel has callbacks
$channel->wait();
while ( $fpmClient->hasUnhandledResponses() )
{
	$requestIds = $fpmClient->getRequestIdsHavingResponse();
	if ( \count( $requestIds ) >= 0 )
	{
		$fpmClient->handleResponses( 5000, ...$requestIds );
		$channel->wait( null, true );
	}
}
