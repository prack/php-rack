<?php

// TODO: Document!
# Middleware that enables conditional GET using If-None-Match and
# If-Modified-Since. The application should set either or both of the
# Last-Modified or Etag response headers according to RFC 2616. When
# either of the conditions is met, the response body is set to be zero
# length and the response status is set to 304 Not Modified.
#
# Applications that defer response body generation until the body's each
# message is received will avoid response body generation completely when
# a conditional GET matches.
#
# Adapted from Michael Klishin's Merb implementation:
# http://github.com/wycats/merb-core/tree/master/lib/merb-core/rack/middleware/conditional_get.rb
class Prack_ConditionalGet
  implements Prack_Interface_MiddlewareApp
{
	private $middleware_app;
	
	// TODO: Document!
	static function with( $middleware_app )
	{
		return new Prack_ConditionalGet( $middleware_app );
	}
	
	// TODO: Document!
	public function __construct( $middleware_app )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		if ( !( Prb::_Array( array( Prb::_String( 'GET' ), Prb::_String( 'HEAD' ) ) )
		          ->contains( $env->get( 'REQUEST_METHOD' ) ) ) )
			return $this->middleware_app->call( $env );
		
		list( $status, $headers, $body ) = $this->middleware_app->call( $env )->raw();
		
		$headers = Prack_Utils_HeaderHash::using( $headers );
		if ( $this->etagMatches( $env, $headers ) || $this->isModifiedSince( $env, $headers ) )
		{
			$status = Prb::_Numeric( 304 );
			$headers->delete( 'Content-Type'   );
			$headers->delete( 'Content-Length' );
			$body = Prb::_Array();
		}
		
		return Prb::_Array( array( $status, $headers, $body ) );
	}
	
	// TODO: Document!
	private function etagMatches( $env, $headers )
	{
		$etag = $headers->get( 'Etag' );
		return ( isset( $etag ) && $etag == $env->get( 'HTTP_IF_NONE_MATCH' ) )
		  ? $etag
		  : null;
	}
	
	// TODO: Document!
	private function isModifiedSince( $env, $headers )
	{
		$last_modified = $headers->get( 'Last-Modified' );
		return ( isset( $last_modified ) && $last_modified == $env->get( 'HTTP_IF_MODIFIED_SINCE' ) )
		  ? $last_modified
		  : null;
	}
}