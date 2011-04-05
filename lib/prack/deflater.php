<?php

// TODO: Document!
class Prack_Deflater
  implements Prack_I_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_Deflater( $middleware_app );
	}
	
	// TODO: Document!
	function __construct( $middleware_app = null )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( &$env )
	{
		list( $status, $headers, $body ) = $this->middleware_app->call( $env );
		
		$utils   = Prack_Utils::singleton();
		$headers = Prack_Utils_HeaderHash::using( $headers );
		
		# Skip compressing empty entity body responses and responses with
		# no-transform set.
		if ( in_array( $status, $utils->statusWithNoEntityBody() )
		     || (bool)preg_match( '/\bno-transform\b/', (string)$headers->get( 'Cache-Control' ) ) )
			return array( $status, $headers->raw(), $body );
		
		$request  = Prack_Request::with( $env );
		$encoding =
		  $utils->selectBestEncoding( array( 'gzip', 'deflate', 'identity' ), $request->acceptEncoding() );
		
		# Set the Vary HTTP header.
		$vary = array_map( 'trim', preg_split( '/,/', (string)$headers->get( 'Vary' ) ) );
		if ( $vary == array( '' ) )
			$vary = array();
		
		if ( !( in_array( '*', $vary ) || in_array( 'Accept-Encoding', $vary ) ) )
		{
			array_push( $vary, 'Accept-Encoding' );
			$headers->set( 'Vary', join( ',', $vary ) );
		}
		
		switch ( $encoding )
		{
			case 'gzip':
				$headers->set( 'Content-Encoding', 'gzip' );
				$headers->delete( 'Content-Length' );
				$mtime = $headers->contains( 'Last-Modified' )
				 ? strtotime( $headers->get( 'Last-Modified' ) )
				 : time();
				return array( $status, $headers->raw(), Prack_Deflater_GzipStream::with( $body, $mtime ) );
			case 'deflate':
				$headers->set( 'Content-Encoding', 'deflate' );
				$headers->delete( 'Content-Length' );
				return array( $status, $headers->raw(), Prack_Deflater_DeflateStream::with( $body ) );
			case 'identity':
				return array( $status, $headers->raw(), $body );
		}
		
		$message = "An acceptable encoding for the requested resource {$request->fullpath()} could not be found.";
		return
		  array(
		    406,
		    array( 'Content-Type' => 'text/plain', 'Content-Length' => (string)strlen( $message ) ),
		    array( $message )
		  );
	}
}