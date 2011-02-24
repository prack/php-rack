<?php

// TODO: Document!
class Prack_Utils_IO_String extends Prack_Utils_IO
  implements Prack_Interface_ReadableStreamlike, Prack_Interface_WritableStreamlike, Prack_Interface_LengthAware
{	
	const MAX_STRING_LENGTH = 1048576; // Maximum size in bytes of in-memory string buffer.
	
	private $string;
	private $length;
	
	// TODO: Document!
	function __construct( $string = '' )
	{
		if ( isset( $string ) && strlen( $string ) > self::MAX_STRING_LENGTH )
			throw new Prack_Error_Runtime_StringTooBigForStringIO();
		
		$this->string = $string;
		$this->length = strlen( $string );
		
		$stream   = fopen( 'php://memory', 'w+b' );
		$writable = is_null( $string ) ? '' : $string;
		
		fputs( $stream, $writable );
		rewind( $stream );
		
		parent::__construct( $stream, true );
	}
	
	// TODO: Document!
	public function read( $length = null, &$buffer = null )
	{
		if ( is_null( $length ) )
			$length = isset( $buffer ) ? self::MAX_STRING_LENGTH - strlen( $buffer ) : self::MAX_STRING_LENGTH;
		return parent::read( $length, $buffer );
	}
	
	// TODO: Document!
	public function write( $buffer )
	{
		$this->length += strlen( $buffer );
		return parent::write( $buffer );
	}
	
	// TODO: Document!
	public function length()
	{
		return $this->length;
	}
	
	// TODO: Document!
	public function string()
	{
		$stream = parent::getStream(); // from parent;
		$curpos = ftell( $stream );
		
		parent::rewind();
		$this->string = $this->read();
		
		fseek( $stream, $curpos );
		
		return Prack_Wrapper_String::with( $this->string );
	}
}
