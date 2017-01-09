<?php declare(strict_types = 1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require(__DIR__ . '/../vendor/autoload.php');

error_log( " [x] Processing {$_POST['number']}\n", 3, sys_get_temp_dir() . '/workers.log' );

sleep( 1 );
