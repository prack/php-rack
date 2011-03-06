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
		
		$status = $get_params->contains( 'status' ) ? (int)$get_params->get( 'status' )->toN()
		                                            : 200;
		
		$response = new Prack_Response(
		  Prack::_String( serialize( $env ) ),
		  $status,
		  Prack::_Hash( array( 'Content-Type' => Prack::_String( 'text/yaml' ) ) )
		);
		
		return $response->toN();
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
	function __construct( $status = 200, $headers = null, $body = null, $eval = null )
	{
		$status = is_null( $status ) ? 200 : (int)$status;
		if ( !is_integer( $status ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $status is not an integer' );
		
		$headers = is_null( $headers )
		  ? Prack::_Hash( array(
		      'Content-Type' => Prack::_String( 'test/plain' )
		    ) )
		  : $headers;
		if ( !( $headers instanceof Prack_Wrapper_Hash ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $headers is not a Prack_Wrapper_Hash' );
		
		$body = is_null( $body )
		  ? Prack::_Array( array( Prack::_String() ) )
		  : $body;
		if ( !( $body instanceof Prack_Interface_Stringable ) && !( $body instanceof Prack_Interface_Enumerable ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $body is neither Prack_Interface_Stringable nor Prack_Interface_Enumerable' );
		
		$eval = is_null( $eval ) ? '' : $eval;
		if ( !is_string( $eval ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $eval must be php-native string' );
		
		$this->status  = $status;
		$this->headers = Prack_Utils_Response_HeaderHash::using( $headers );
		$this->body    = $body;
		$this->eval    = $eval;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		if ( $this->eval )
			eval( $this->eval );
		
		return array( $this->status, $this->headers, $this->body );
	}
	
	public function setEval( $eval )
	{
		$this->eval = $eval;
	}
}

// TODO: Document!
class TestHelper
{
	// TODO: Document!
	static function gibberish( $length = 128 )
	{
		$aZ09 = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ), range( 0, 9 ) );
		$out  = '';
		for( $c = 0; $c < $length; $c++ )
			$out .= (string)$aZ09[ mt_rand( 0, count( $aZ09 ) - 1 ) ];
		return Prack::_String( $out );
	}
}