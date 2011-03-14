<?php
class Prack_DelegateFor_Response
{	
	// TODO: Document!
	static function isInvalid( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status < 100 || $status >= 600 );
	}
	
	// TODO: Document!
	static function isInformational( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status >= 100 && $status < 200 );
	}
	
	// TODO: Document!
	static function isSuccessful( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status >= 200 && $status < 300 );
	}
	
	// TODO: Document!
	static function isRedirection( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status >= 300 && $status < 400 );
	}
	
	// TODO: Document!
	static function isClientError( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status >= 400 && $status < 500 );
	}
	
	// TODO: Document!
	static function isServerError( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return ( $status >= 500 && $status < 600 );
	}
	
	// TODO: Document!
	static function isOK( $response )
	{
		return ( (int)$response->getStatus()->raw() == 200 );
	}
	
	// TODO: Document!
	static function isForbidden( $response )
	{
		return ( (int)$response->getStatus()->raw() == 403 );
	}
	
	// TODO: Document!
	static function isNotFound( $response )
	{
		return ( (int)$response->getStatus()->raw() == 404 );
	}
	
	// TODO: Document!
	static function isRedirect( $response )
	{
		$status = (int)$response->getStatus()->raw();
		return in_array( (int)$response->getStatus()->raw(), array( 301, 302, 303, 307 ) );
	}
	
	// TODO: Document!
	// Our delegation strategy prevents this method from ever getting called.
	static function isEmpty( $response )
	{
		return in_array( (int)$response->getStatus()->raw(), array( 201, 204, 304 ) );
	}
	
	// TODO: Document!
	static function contains( $response, $header )
	{
		return $response->getHeaders()->contains( $header );
	}
	
	// TODO: Document!
	static function contentType( $response )
	{
		return $response->get( 'Content-Type' );
	}
	
	// TODO: Document!
	static function contentLength( $response )
	{
		$content_length = $response->get( 'Content-Length' );
		return is_null( $content_length ) ? null : (int)$content_length->raw();
	}
	
	// TODO: Document!
	static function location( $response )
	{
		return $response->get( 'Location' );
	}
}
