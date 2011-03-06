<?php

// TODO: Document!
class Prack_Utils_IO_String extends Prack_Utils_IO
  implements Prack_Interface_ReadableStreamlike, Prack_Interface_WritableStreamlike, Prack_Interface_LengthAware
{	
	const MAX_STRING_LENGTH = 1048576; // Maximum size in bytes of in-memory string buffer.
	
	private $string;
	
	// TODO: Document!
	function __construct( $string = null )
	{
		$string = is_null( $string ) ? Prack::_String() : $string;
		if ( !( $string instanceof Prack_Interface_Stringable ) )
			throw new Prack_Error_Type( 'FAILSAFE: __construct $string is not a Prack_Interface_Stringable' );
		
		if ( $string->length() > self::MAX_STRING_LENGTH )
			throw new Prack_Error_Runtime_StringTooBigForStringIO();
		
		$this->string = $string;
		$this->length = $string->length();
		
		$stream = fopen( 'php://memory', 'w+b' );
		
		fputs( $stream, $string->toN() );
		rewind( $stream );
		
		parent::__construct( $stream, true );
	}
	
	// TODO: Document!
	public function read( $length = null, $buffer = null )
	{
		if ( is_null( $length ) )
			$adjusted_length = isset( $buffer ) ? self::MAX_STRING_LENGTH - $buffer->length() : self::MAX_STRING_LENGTH;
		else
			$adjusted_length = $length;
		
		$result = parent::read( $adjusted_length, $buffer );
		
		return ( is_null( $length ) && is_null( $result ) ) ? Prack::_String() : $result;
	}
	
	// TODO: Document!
	public function write( $buffer )
	{
		$this->length += $buffer->length();
		return parent::write( $buffer );
	}
	
	// TODO: Document!
	public function length()
	{
		return $this->string->length();
	}
	
	// TODO: Document!
	public function string()
	{
		$stream = parent::getStream(); // from parent.
		$curpos = ftell( $stream );
		
		parent::rewind();
		$this->string = $this->read();
		
		fseek( $stream, $curpos );
		
		return $this->string;
	}
}
