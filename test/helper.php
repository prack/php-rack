<?php

// TODO: Document!
class Prack_Test_EnvSerializer
  implements Prack_Interface_MiddlewareApp
{
	// TODO: Document!
	public function call( $env )
	{
		$request = new Prack_Request( $env );
		
		$env->set( 'mock.postdata', $env->get( 'rack.input' )->read() );
		$get_params = $request->GET();
		
		if ( $get_params->contains( 'error' ) )
		{
			$env->get( 'rack.errors' )->puts( $get_params->get( 'error' ) );
			$env->get( 'rack.errors' )->flush();
		}
		
		$status = $get_params->contains( 'status' ) ? $get_params->get( 'status' )->toN()
		                                            : Prb::_Numeric( 200 );
		
		$response = new Prack_Response(
		  Prb::_String( serialize( $env ) ),
		  $status,
		  Prb::_Hash( array( 'Content-Type' => Prb::_String( 'text/yaml' ) ) )
		);
		
		return $response->toA();
	}
}

// TODO: Document!
class Prack_Test_Echo
  implements Prack_Interface_MiddlewareApp
{
	private $status;
	private $headers;
	private $body;
	private $eval;
	
	// TODO: Document!
	function __construct( $status = null, $headers = null, $body = null, $eval = null )
	{
		$status  = is_null( $status ) ? Prb::_Numeric( 200 ) : $status;
		$headers = is_null( $headers )
		  ? Prb::_Hash( array(
		      'Content-Type' => Prb::_String( 'test/plain' )
		    ) )
		  : $headers;
		$body = is_null( $body )
		  ? Prb::_Array( array( Prb::_String() ) )
		  : $body;
		
		$eval = is_null( $eval ) ? '' : $eval;
		if ( !is_string( $eval ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $eval must be php-native string' );
		
		$this->status  = $status;
		$this->headers = Prack_Utils_HeaderHash::using( $headers );
		$this->body    = $body;
		$this->eval    = $eval;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		if ( $this->eval )
			eval( $this->eval );
		
		return Prb::_Array( array( $this->status, $this->headers, $this->body ) );
	}
	
	public function setEval( $eval )
	{
		$this->eval = $eval;
	}
}

// TODO: Document!
class Prack_TestHelper
{
	// TODO: Document!
	static function gibberish( $length = 128 )
	{
		$aZ09 = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ), range( 0, 9 ) );
		$out  = '';
		for( $c = 0; $c < $length; $c++ )
			$out .= (string)$aZ09[ mt_rand( 0, count( $aZ09 ) - 1 ) ];
		return Prb::_String( $out );
	}
}