<?php

// TODO: Document!
class Prack_Logger
  implements Prack_I_MiddlewareApp
{
	private $app;
	private $level;
	
	// TODO: Document!
	static function with( $app, $level = null )
	{
		return new Prack_Logger( $app, $level );
	}
	
	// TODO: Document!
	function __construct( $app, $level = null )
	{
		if ( is_null( $level ) )
			$level = Prb_Logger::INFO;
		
		$this->app   = $app;
		$this->level = $level;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$logger = Prb_Logger::with( $env[ 'rack.errors' ] );
		$logger->setLevel( $this->level );
		$env[ 'rack.logger' ] = $logger;
		
		try
		{
			$response = $this->app->call( $env );
		}
		catch( Exception $e )
		{
			$logger->close();
			throw $e;
		}
		
		$logger->close();
		return $response;
	}
}