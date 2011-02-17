<?php

class Prack_ErrorLogger
{	
	const ALL   = 0;
	const DEBUG = 5;
	const INFO  = 4;
	const WARN  = 3;
	const ERROR = 2;
	const FATAL = 1;
	
	private $stream;
	private $level;
	
	
	static public function standard( $level = self::ALL )
	{
		static $logger = null;
		
		if ( is_null( $logger ) )
		{
			$stderr = fopen( 'php://stderr', 'w' );
			$logger = new Prack_ErrorLogger( $stderr, $level );
		}
		
		return $logger;
	}
	
	
	function __construct( $stream = null, $level = self::ALL )
	{
		$this->stream = $stream;
		$this->level  = $level;
	}
	
	
	public function puts( $value )
	{
		if ( is_object( $value ) && !method_exists( $value, '__toString' ) )
			throw new Prack_Error_ErrorLogger_UnloggableValue();
		
		fwrite( $this->stream, (string)$value );
	}
	
	
	// Not even sure how these two methods differ in approach in the context of PHP.
	// Leaving in for completeness per Rack spec.
	public function write( $value )
	{
		return $this->puts( $value );
	}
	
	
	public function flush()
	{
		flush( $this->stream );
	}
	
	public function close()
	{
		# Per the Rack Specification
		throw new Prack_Error_ErrorLogger_StreamCannotBeClosed();
	}
}