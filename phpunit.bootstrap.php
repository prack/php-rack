<?php

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );

$pwd = dirname( __FILE__ );

// Prack depends on Prb to run. We have to load its autoloader.
//
// This bootstrapper assumes the following filestructure to work:
//   (enclosing folder, possibly a /lib directory )
//   \_ php-rack (you are here, in phpunit.bootstrap.php.)
//   \_ php-rb  
//
// Consequently, it looks in '../php-rb/' for 'autoload.php'.
// You can override this by setting the environment variable
// PRACK_PRB_AUTOLOAD_PATH like so, when you run your tests:
//   $ PRACK_PRB_AUTOLOAD_PATH="/path/to/php-rb/autoload.php" phpunit
$prb_autoload_file_path =
  getenv( 'PRACK_PRB_AUTOLOAD_PATH' ) ? getenv( 'PRACK_PRB_AUTOLOAD_PATH' )
                                      : join( DIRECTORY_SEPARATOR, array( $pwd, '..', 'php-rb', 'autoload.php' ) );

if ( !file_exists( $prb_autoload_file_path ) )
	die( 'Cannot load php-rb autoload file. See phpunit.bootstrap.php source for instructions.' );

require_once $prb_autoload_file_path;
require_once join( DIRECTORY_SEPARATOR, array( $pwd, 'autoload.php'       ) );
require_once join( DIRECTORY_SEPARATOR, array( $pwd, 'test', 'helper.php' ) );
