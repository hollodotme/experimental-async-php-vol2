<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\AsyncPhp;

require __DIR__ . '/../vendor/autoload.php';

error_log(
	" [x] Processing {$_POST['number']} from daemon {$_POST['daemonId']}\n",
	3,
	sys_get_temp_dir() . '/workers.log'
);

usleep( random_int( 200000, 500000 ) );

echo 'SUCCEEDED';
