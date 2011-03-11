<?php

// TODO: Document!
class Prack_RewindableInput 
  implements Prb_Interface_ReadableStreamlike, Prb_Interface_LengthAware
{
	const CHUNK_SIZE = 4096; // 4KB chunk size for read
	
	private $io;
	private $rewindable_io;
	private $unlinked;
	private $length;
	private $is_closed;
	
	// TODO: Document!
	static function with( $io )
	{
		return new Prack_RewindableInput( $io );
	}
	
	// TODO: Document!
	function __construct( $io )
	{
		$this->io            = $io;
		$this->rewindable_io = null;
		$this->unlinked      = false;
		$this->length        = 0;
	}
	
	// TODO: Document!
	public function gets()
	{
		if ( $this->isReadable() )
		{
			if ( is_null( $this->rewindable_io ) )
				$this->makeRewindable();
			
			return $this->rewindable_io->gets();
		}
		
		throw new Prb_Exception_IO( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function read( $length = null, $buffer = null )
	{
		if ( $this->isReadable() )
		{
			if ( is_null( $this->rewindable_io ) )
				$this->makeRewindable();
			
			return $this->rewindable_io->read( $length, $buffer );
		}
		
		throw new Prb_Exception_IO( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		if ( $this->isReadable() )
		{
			if ( is_null( $this->rewindable_io ) )
				$this->makeRewindable();
			
			return $this->rewindable_io->each( $callback );
		}
		
		throw new Prb_Exception_IO( 'stream is not readable' );
	}
	
	// TODO: Document!
	public function rewind()
	{
		if ( is_null( $this->rewindable_io ) )
			$this->makeRewindable();
		
		return $this->rewindable_io->rewind();
	}
	
	// TODO: Document!
	public function length()
	{
		if ( is_null( $this->rewindable_io ) )
			$this->makeRewindable();
		
		return $this->length;
	}
	
	public function isReadable()
	{
		return !$this->is_closed;
	}
	
	// TODO: Document!
	# Closes this RewindableInput object without closing the originally
	# wrapped IO object. Cleans up any temporary resources that this RewindableInput
	# has created.
	#
	# This method may be called multiple times. It does nothing on subsequent calls.
	public function close()
	{
		if ( isset( $this->rewindable_io ) )
		{
			$this->rewindable_io->close( !$this->unlinked );
			$this->rewindable_io = null;
			$this->is_closed     = true;
		}
	}
	
	// TODO: Document!
	public function getRewindableIO()
	{
		return $this->rewindable_io;
	}
	
	// TODO: Document!
	private function makeRewindable()
	{
		$this->rewindable_io = Prb_IO::withTempfile( 'rack-rewindable-input' );
		$this->rewindable_io->chmod( 0000 );
		
		if ( $this->filesystemHasPosixSemantics() )
		{
			$this->rewindable_io->unlink();
			// FIXME: Should throw an exception here if the io ends up closed.
			// How to check this in PHP, there's no is_open()?
			$this->unlinked = true;
		}
		
		while ( $buffer = $this->io->read( self::CHUNK_SIZE ) )
		{
			$entire_buffer_written_out = false;
			while ( $entire_buffer_written_out == false )
			{
				$written = $this->rewindable_io->write( $buffer );
				$entire_buffer_written_out = ( $written == $buffer->length() );
				if ( $entire_buffer_written_out == false )
					$buffer = $written->slice( $written - 1, $buffer->length() - $written );
			}
			$this->length += $buffer->length();
		}
		$this->rewindable_io->rewind();
	}
	
	// TODO: Document!
	private function filesystemHasPosixSemantics()
	{
		// TODO: Add platform check, excluding non-posix systems.
		return true;
	}
}