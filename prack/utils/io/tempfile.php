<?php

// TODO: Document!
class Prack_Utils_IO_Tempfile extends Prack_Utils_IO
  implements Prack_Interface_ReadableStreamlike, Prack_Interface_WritableStreamlike
{
	private $tempfile_path;
	
	// TODO: Document!
	# Buffer all data into a tempfile. Since this tempfile is private to this
	# process, we chmod it so that nobody else can read or write
	# it. On POSIX filesystems we also unlink the file so that it doesn't
	# even have a file entry on the filesystem anymore, though we can still
	# access it because we have the file handle open.
	function __construct( $prefix = 'prack-tmp' )
	{
		$this->tempfile_path = tempnam( sys_get_temp_dir(), $prefix );
		parent::__construct( fopen( $this->tempfile_path, 'w+b' ), true );
	}
	
	// TODO: Document!
	public function read( $length = null, &$buffer = null )
	{
		return parent::read( $length, $buffer );
	}
	
	// TODO: Document!
	public function write( $buffer )
	{
		return parent::write( $buffer );
	}
	
	// TODO: Document!
	public function rewind()
	{
		parent::rewind();
	}
	
	// TODO: Document!
	public function chmod( $permissions )
	{
		chmod( $this->tempfile_path, $permissions );
	}
	
	// TODO: Document!
	public function unlink()
	{
		unlink( $this->tempfile_path );
	}
	
	// TODO: Document!
	public function close( $unlink = false )
	{
		parent::close();
		if ( $unlink )
			unlink( $this->tempfile_path );
	}
	
	// TODO: Document!
	public function getPath()
	{
		return $this->tempfile_path;
	}
}
