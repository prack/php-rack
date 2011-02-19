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
	static public function withString( $string = null )
	{
		return new Prack_Utils_IO_String( $string );
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
	public function read( $length = null, &$buffer = null )
	{
		if ( $this->isReadable() )
		{
			$remaining = is_null( $length ) ? PHP_INT_MAX : $length;
			$internal  = '';
			$read_size = min( $remaining, self::CHUNK_SIZE );
			
			// Chunked read, as fread() is limited to 8K of pro per call.
			while ( !feof( $this->stream ) && $remaining > 0 && $temp = fread( $this->stream, $read_size ) )
			{
				$remaining -= strlen( $temp );
				$internal  .= $temp;
				$read_size  = min( $remaining, self::CHUNK_SIZE );
			}
			
			$buffer = isset( $buffer ) ? $buffer.$internal : $internal;
			
			return $buffer;
		}
		
		throw new Prack_Error_Runtime_IOError( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function gets( $limit = null )
	{
		if ( $this->isReadable() )
		{
			$result = is_null( $limit) ? fgets( $this->stream ) : fgets( $this->stream, $limit );
			
			if ( is_string( $result ) )
				$this->line_no += 1;
			else if ( $result === false )
				$result = null;
			
			return $result;
		}
		
		throw new Prack_Error_Runtime_IOError( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( $this->isReadable() )
		{
			if ( !is_callable( $callback ) )
				throw new Prack_Error_Runtime_CallbackInvalid();
			
			$lines = array();
			while ( $line = $this->gets() )
				array_push( $lines, $line );
			
			array_walk( $lines, $callback );
			
			return;
		}
		
		throw new Prack_Error_Runtime_IOError( 'stream is not readable' );
	}
	
	public function write( $buffer )
	{
		if ( $this->isWritable() )
			return fwrite( $this->stream, $buffer );
		
		throw new Prack_Error_Runtime_IOError( 'stream is not writable' );
	}
	
	// TODO: Document!
	public function rewind()
	{
		rewind( $this->stream );
	}
	
	// TODO: Document!
	public function isReadable()
	{
		return ( $this instanceof Prack_Utils_IO_IReadable && $this->is_readable );
	}
	
	// TODO: Document!
	public function closeRead()
	{
		$this->is_readable = false;
	}
	
	// TODO: Document!
	public function isWritable()
	{
		return ( $this instanceof Prack_Utils_IO_IWritable && $this->is_writable );
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
	
	public function getLineNo()
	{
		return $this->line_no;
	}
}