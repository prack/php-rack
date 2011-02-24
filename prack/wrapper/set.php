<?php

// TODO: Document!
class Prack_Wrapper_Set extends Prack_Wrapper_Abstract_Collection
  implements Prack_Interface_Enumerable
{
	// TODO: Document!
	static function with( $array )
	{
		return new Prack_Wrapper_Set( $array );
	}
	
	// TODO: Document!
	public function each( $callback )
	{
		parent::each( $callback );
		
		foreach ( $this->array as $key => $item )
			call_user_func( $callback, $item );
	}
}
