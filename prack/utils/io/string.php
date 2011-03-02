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
		if ( is_null( $string ) )
			$string = Prack::_String();
		else if ( !( $string instanceof Prack_Interface_Stringable ) )
		{
			$string_type = is_object( $string ) ? get_class( $string) : gettype( $string );
			throw new Prack_Error_Type( "cannot create string io stream with provided {$string_type}" );
		}
		
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
			$length = isset( $buffer ) ? self::MAX_STRING_LENGTH - $buffer->length() : self::MAX_STRING_LENGTH;
		return parent::read( $length, $buffer );
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
