<?php

// TODO: Document!
abstract class Prack_Utils_IO
{
	const CHUNK_SIZE = 4096; // 4KB chunk size for read
	
	private $stream;
	private $line_no;
	private $close_underlying;
	private $is_readable;
	private $is_writable;
	
	// TODO: Document!
	static function withTempfile( $prefix = 'prack-tmp', $opaque = true )
	{
		return new Prack_Utils_IO_Tempfile( $prefix, $opaque );
	}
	
	// TODO: Document!
	static function withString( $string = null )
	{
		return new Prack_Utils_IO_String( $string );
	}
	
	// TODO: Document!
	static function readlines( $filename )
	{
		$buffer = file_get_contents( $filename );
		return Prack::_Array( preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $buffer ) );
	}
	
	// TODO: Document!
	function __construct( $stream, $close_underlying = false )
	{
		$this->stream           = $stream;
		$this->line_no          = 0;
		$this->close_underlying = $close_underlying;
		$this->is_readable      = true; // FIXME: Ascertain whether a stream is readable in an actual way.
		$this->is_writable      = true; // FIXME: Same.
	}
	
	// TODO: Document!
	public function read( $length = null, $buffer = null )
	{
		if ( $this->isReadable() )
		{
			$remaining = is_null( $length ) ? PHP_INT_MAX : $length;
			$read_size = min( $remaining, self::CHUNK_SIZE );
			
			$buffer = is_null( $buffer ) ? Prack::_String() : $buffer;
			if ( !( $buffer instanceof Prack_Wrapper_String ) )
				throw new Prack_Error_Lint( 'read $buffer not a string' );
			
			if ( feof( $this->stream ) )
				return is_null( $length ) ? Prack::_String() : null;
			
			// Chunked read, as fread() is limited to 8K of data per call.
			while ( !feof( $this->stream ) && $remaining > 0 && $temp = fread( $this->stream, $read_size ) )
			{
				$remaining -= strlen( $temp );
				$read_size  = min( $remaining, self::CHUNK_SIZE );
				$buffer->concat( Prack::_String( $temp ) );
			}
			
			return $buffer;
		}
		
		throw new Prack_Error_IO( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function gets()
	{
		global $g_prack_last_gets;
		
		if ( $this->isReadable() )
		{
			$result = fgets( $this->stream );
			
			if ( is_string( $result ) )
			{
				$result = Prack::_String( $result );
				$this->line_no += 1;
			}
			else if ( $result === false )
				$result = null;
			
			$g_prack_last_gets = $result;
			
			return $result;
		}
		
		throw new Prack_Error_IO( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( $this->isReadable() )
		{
			if ( !is_callable( $callback ) )
				throw new Prack_Error_Callback();
			
			$lines = array();
			while ( $line = $this->gets() )
				array_push( $lines, $line );
			
			array_walk( $lines, $callback );
			
			return;
		}
		
		throw new Prack_Error_IO( 'stream is not readable' );
	}
	
	public function write( $buffer )
	{
		if ( $this->isWritable() )
			return fwrite( $this->stream, $buffer->toN() );
		
		throw new Prack_Error_IO( 'stream is not writable' );
	}
	
	// TODO: Document!
	# Writes the given objects to <em>ios</em> as with
	# <code>IO#print</code>. Writes a record separator (typically a
	# newline) after any that do not already end with a newline sequence.
	# If called with an array argument, writes each element on a new line.
	# If called without arguments, outputs a single record separator.
	public function puts()
	{
		global $g_prack_f_sep;
		
		$args = func_get_args();
		foreach ( $args as $printable )
		{
			if ( $printable instanceof Prack_Wrapper_Array )
			{
				$callback  = create_function( '$i', 'return $i->strip();' );
				$printable = $printable->each( $callback )->join( $g_prack_f_sep );
			}
			
			$this->_print( $printable );
		}
	}
	
	// TODO: Document!
	# Writes the given object(s) to <em>ios</em>. The stream must be
	# opened for writing. If the output field separator (<code>$,</code>)
	# is not <code>nil</code>, it will be inserted between each object.
	# If the output record separator (<code>$\\</code>)
	# is not <code>nil</code>, it will be appended to the output. If no
	# arguments are given, prints <code>$_</code>. Objects that aren't
	# strings will be converted by calling their <code>to_s</code> method.
	# With no argument, prints the contents of the variable <code>$_</code>.
	# Returns <code>nil</code>.
	public function _print()
	{
		global $g_prack_last_gets;
		global $g_prack_f_sep;
		global $g_prack_o_sep;
		
		$args = func_get_args();
		
		if ( count( $args ) == 0 )
			$args = array( $g_prack_last_gets );

		foreach ( $args as $printable )
		{
			$this->write( $printable );
			
			if ( isset( $g_prack_f_sep ) )
				$this->write( $g_prack_f_sep );
		}
		
		if ( isset( $g_prack_o_sep ) )
			$this->write( $g_prack_o_sep );
	}
	
	// TODO: Document!
	public function flush()
	{
		flush( $this->stream );
	}
	
	// TODO: Document!
	public function rewind()
	{
		rewind( $this->stream );
	}
	
	// TODO: Document!
	public function isReadable()
	{
		return ( $this instanceof Prack_Interface_ReadableStreamlike && $this->is_readable );
	}
	
	// TODO: Document!
	public function closeRead()
	{
		$this->is_readable = false;
	}
	
	// TODO: Document!
	public function isWritable()
	{
		return ( $this instanceof Prack_Interface_WritableStreamlike && $this->is_writable );
	}
	
	// TODO: Document!
	public function closeWrite()
	{
		$this->is_writable = false;
	}
	
	// TODO: Document!
	public function isClosed()
	{
		return ( $this->isReadable() == false && $this->isWritable() == false );
	}
	
	// TODO: Document!
	public function close()
	{
		$this->closeRead();
		$this->closeWrite();
		
		if ( $this->stream && $this->close_underlying )
		{
			fclose( $this->stream );
			$this->stream = null;
		}
	}
	
	// TODO: Document!
	public function getLineNo()
	{
		return $this->line_no;
	}
	
	// TODO: Document!
	protected function getStream()
	{
		return $this->stream;
	}
}