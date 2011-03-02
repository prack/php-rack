<?php

// TODO: Document!
class Prack_MockTest_MiddlewareApp
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
class TestEcho
  implements Prack_Interface_MiddlewareApp
{
	private $status;
	private $headers;
	private $body;
	private $eval;
	
	// TODO: Document!
	function __construct( $status = 200, $headers = null, $body = null )
	{
		if ( is_null( $headers ) )
			$headers = Prack::_Hash( array(
			  'Content-Type' => Prack::_String( 'test/plain' )
			) );
		
		$this->status  = $status;
		$this->headers = $headers;
		$this->body    = $body instanceof Prack_Interface_Enumerable ? $body : Prack::_Array( $body );
		$this->eval    = null;
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
		return $out;
	}
}