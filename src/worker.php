<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require(__DIR__ . '/../vendor/autoload.php');

error_log( ($_POST['timestamp'] . "\n"), 3, __DIR__ . '/../logs/workers.log' );

sleep( 1 );
