<?php

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );

require_once( 'SymfonyComponents/YAML/sfYaml.php' );
require_once( 'SymfonyComponents/YAML/sfYamlDumper.php' );
require_once( dirname(__FILE__).DIRECTORY_SEPARATOR.'autoload.php' );
