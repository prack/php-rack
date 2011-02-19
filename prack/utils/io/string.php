<?php

// TODO: Document!
class Prack_Utils_IO_String extends Prack_Utils_IO
  implements Prack_Utils_IO_IReadable, Prack_Utils_IO_IWritable, Prack_Utils_IO_IRewindable, Prack_Utils_IO_ILengthAware
{	
	const MAX_STRING_LENGTH = 1048576; // Maximum size in bytes of in-memory string buffer.
	
	private $length;
	
	// TODO: Document!
	function __construct( $string = null )
	{
		if ( isset( $string ) && strlen( $string ) > self::MAX_STRING_LENGTH )
			throw new Prack_Error_Runtime_StringTooBigForStringIO();
		
		$this->length = strlen( $string);
		
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
	public function length()
	{
		return $this->length;
	}
}
