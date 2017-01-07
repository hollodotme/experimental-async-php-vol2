<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;

require(__DIR__ . '/../vendor/autoload.php');

$redisHost = '127.0.0.1';
$redisPort = 6379;

$redis     = new \Redis();
$connected = $redis->connect( $redisHost, $redisPort );

if ( $connected )
{
	echo "Connected to redis on {$redisHost}:{$redisPort}\n";

	$redis->subscribe(
		[ 'commands' ],
		function ( \Redis $redis, string $channel, string $message )
		{
			$messageArray = json_decode( $message );
			$body         = http_build_query( $messageArray );

			$connection = new UnixDomainSocket( 'unix:///var/run/php/php7.1-fpm-commands.sock' );
			$fpmClient  = new Client( $connection );
			$processId  = $fpmClient->sendAsyncRequest(
				[
					'GATEWAY_INTERFACE' => 'FastCGI/1.0',
					'REQUEST_METHOD'    => 'POST',
					'SCRIPT_FILENAME'   => '/vagrant/src/worker.php',
					'SERVER_SOFTWARE'   => 'php/fcgiclient',
					'REMOTE_ADDR'       => '127.0.0.1',
					'REMOTE_PORT'       => '9985',
					'SERVER_ADDR'       => '127.0.0.1',
					'SERVER_PORT'       => '80',
					'SERVER_NAME'       => 'myServer',
					'SERVER_PROTOCOL'   => 'HTTP/1.1',
					'CONTENT_TYPE'      => 'application/x-www-form-urlencoded',
					'CONTENT_LENGTH'    => mb_strlen( $body ),
				],
				$body
			);

			echo "Spawned process with ID: {$processId}\n";
		}
	);
}
else
{
	echo "Could not connect to redis.\n";
}
