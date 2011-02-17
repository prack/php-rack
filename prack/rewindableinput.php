<?php

class Prack_RewindableInput
{
	const DEFAULT_READ_SIZE = 1024;
	
	private $io;
	private $rewindable_io;
	private $unliked;
	
	function __construct( $io )
	{
		$this->io            = $io;
		$this->rewindable_io = null;
		$this->unlinked      = false;
	}
	
	public function gets()
	{
		if ( !$this->rewindable_io )
			$this->makeRewindable();
		
		return fgets( $this->rewindable_io );
	}
	
	public function read( $length = self::DEFAULT_READ_SIZE )
	{
		if ( !$this->rewindable_io )
			$this->makeRewindable();
		
		return fread( $this->rewindable_io, $length );
	}
	
	public function each( $callback = null )
	{
		if ( !$this->rewindable_io )
			$this->makeRewindable();
		
		if ( is_callable( $callback ) )
		{
			while ( $line = fgets( $this->rewindable_io ) )
				call_user_func( $callback, $line );
		}
	}
	
	public function rewind()
	{
		if ( !$this->rewindable_io )
			$this->makeRewindable();
		
		rewind( $this->rewindable_io );
	}
	
	# Closes this RewindableInput object without closing the originally
	# wrapped IO oject. Cleans up any temporary resources that this RewindableInput
	# has created.
	#
	# This method may be called multiple times. It does nothing on subsequent calls.
	public function close()
	{
		if ( isset( $this->rewindable_io ) )
			@fclose( $this->rewindable_io );
		$this->rewindable_io = null;
	}
	
	public function getRewindableIO()
	{
		return $this->rewindable_io;
	}
	
	private function makeRewindable()
	{
		# Buffer all data into a tempfile. Since this tempfile is private to this
		# RewindableInput object, we chmod it so that nobody else can read or write
		# it. On POSIX filesystems we also unlink the file so that it doesn't
		# even have a file entry on the filesystem anymore, though we can still
		# access it because we have the file handle open.
		$tempfile_path       = tempnam( sys_get_temp_dir(), 'prack' );
		$this->rewindable_io = fopen( $tempfile_path, 'w+b' );
		
		chmod( $tempfile_path, 0000 );
		
		if ( $this->filesystemHasPosixSemantics() )
		{
			unlink( $tempfile_path );
			$this->unlinked = true;
		}
		
		while ( $buffer = fread( $this->io, self::DEFAULT_READ_SIZE * 4 ) )
		{
			$entire_buffer_written_out = false;
			while ( !$entire_buffer_written_out )
			{
				$written = fwrite( $this->rewindable_io, $buffer );
				$entire_buffer_written_out = ( $written == strlen( $buffer ) );
				if ( !$entire_buffer_written_out )
					$buffer = substr( $written - 1, strlen( $buffer ) - $written );
			}
		}
		rewind( $this->rewindable_io );
	}
	
	private function filesystemHasPosixSemantics()
	{
		// TODO: Add platform check, excluding non-posix systems.
		return true;
	}
}