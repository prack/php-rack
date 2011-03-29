<?php

// TODO: Document!
class Prack_Mock_FatalWarner
  implements Prb_I_WritableStreamlike
{
	// TODO: Document!
	public function puts()
	{
		$args = func_get_args();
		throw new Prack_Exception_Mock_Response_FatalWarning( $args[ 0 ] );
	}
	
	// TODO: Document!
	public function write( $warning )
	{
		throw new Prack_Exception_Mock_Response_FatalWarning( $warning );
	}
	
	// TODO: Document!
	public function flush()
	{
		// No-op.
		return true;
	}
	
	// TODO: Document!
	public function string()
	{
		return '';
	}
}