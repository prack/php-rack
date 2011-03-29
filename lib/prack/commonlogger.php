<?php

// TODO: Document!
# Rack::CommonLogger forwards every request to an +app+ given, and
# logs a line in the Apache common log format to the +logger+, or
# rack.errors by default.
class Prack_CommonLogger
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	private $logger;
	
	// TODO: Document!
	static function format()
	{
		static $format = null;
		
		if ( is_null( $format ) )
			$format = '%s - %s [%s] "%s %s%s %s" %d %s %0.4f\n';
		
		return $format;
	}
	
	// TODO: Document!
	static function with( $middleware_app, $logger = null )
	{
		return new Prack_CommonLogger( $middleware_app, $logger );
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $logger = null )
	{
		$this->middleware_app = $middleware_app;
		$this->logger         = $logger;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		$began_at = Prb::Time();
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		
		$headers = Prack_Utils_HeaderHash::using( $headers );
		
		$this->log( $env, $status, $headers, $began_at );
		
		return array( $status, $headers->raw(), $body );
	}
	
	// TODO: Document!
	private function log( $env, $status, $headers, $began_at )
	{
		$now    = Prb::Time();
		$length = $this->extractContentLength( $headers );
		
		$remote_addr = @$env['HTTP_X_FORWARDED_FOR' ]
		  ? $env[ 'HTTP_X_FORWARDED_FOR' ]
		  : ( @$env[ 'REMOTE_ADDR' ] ? $env[ 'REMOTE_ADDR' ] : '-' );
		$remote_user = @$env[ 'REMOTE_USER' ] ? $env[ 'REMOTE_USER' ] : '-';
		
		$logger = isset( $this->logger ) ? $this->logger : $env[ 'rack.errors' ];
		$logger->write(
		  sprintf( self::format(),
		    $remote_addr,
		    $remote_user,
		    $now->strftime( '%d/%b/%Y %H:%M:%S' ),
		    $env[ 'REQUEST_METHOD' ],
		    $env[ 'PATH_INFO'      ],
		    ( @$env[ 'QUERY_STRING' ] && !(string)$env[ 'QUERY_STRING' ] == '' )
		      ? '?'.$env[ 'QUERY_STRING' ]
		      : '',
		    (string)@$env[ 'HTTP_VERSION' ],
		    substr( (string)$status, 0, 3 ),
		    $length,
		    $now->raw() - $began_at->raw()
		  )
		);
	}
	
	// TODO: Document!
	private function extractContentLength( $headers )
	{
		if ( !$headers->contains( 'Content-Length' ) )
			return '-';
		
		return ( $headers->get( 'Content-Length' ) == '0' )
		  ? '-'
		  : $headers->get( 'Content-Length' );
	}
}