<?php

// TODO: Document!
class Prack_Test_EnvSerializer
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	public function call( &$env )
	{
		$env[ 'mock.postdata' ] = $env[ 'rack.input' ]->read();
		
		$get_params = Prack_Request::with( $env )->GET();
		if ( @$get_params[ 'error' ] )
		{
			$env[ 'rack.errors' ]->puts( $get_params[ 'error' ] );
			$env[ 'rack.errors' ]->flush();
		}
		
		$body    = serialize( $env );
		$status  = @$get_params[ 'status' ] ? $get_params[ 'status' ] : 200;
		$headers = array( 'Content-Type' => 'text/yaml' );
		
		return Prack_Response::with( $body, $status, $headers )->finish();
	}
}

// TODO: Document!
class Prack_Test_Echo
  implements Prack_I_MiddlewareApp
{
	private $status;
	private $headers;
	private $body;
	private $eval;
	
	// TODO: Document!
	function __construct( $status = 200, $headers = array(), $body = array(), $eval = null )
	{
		$status  = is_null( $status  ) ? 200 : (int)$status;
		$headers = is_null( $headers ) ? array( 'Content-Type' => 'test/plain' ) : (array)$headers;
		$eval    = is_null( $eval    ) ? '' : $eval;
		
		if ( !is_string( $eval ) )
			throw new Prb_Exception_Type( 'FAILSAFE: __construct $eval must be string' );
		
		$this->status  = $status;
		$this->headers = $headers;
		$this->body    = $body;
		$this->eval    = $eval;
	}
	
	// TODO: Document!
	public function call( &$env )
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
class Prack_TestHelper
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