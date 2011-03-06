<?php

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );

require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'autoload.php' );
require_once join( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), 'test', 'testhelper.php' ) );
