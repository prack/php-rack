<?php

// TODO: Document!
class Prack_Utils_IO_Tempfile extends Prack_Utils_IO_File
  implements Prack_Interface_ReadableStreamlike, Prack_Interface_WritableStreamlike
{
	// TODO: Document!
	static function generatePath( $prefix = 'prack-tmp' )
	{
		return tempnam( sys_get_temp_dir(), $prefix );
	}
	
	// TODO: Document!
	function __construct( $prefix = 'prack-tmp' )
	{
		parent::__construct( self::generatePath( $prefix ), parent::TRUNCATE_AND_READWRITE );
	}
	
	// TODO: Document!
	public function close( $unlink = false )
	{
		parent::close();
		if ( $unlink )
			unlink( $this->getPath() );
	}
}
