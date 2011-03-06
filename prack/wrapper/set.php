<?php

// TODO: Document!
class Prack_Wrapper_Set extends Prack_Wrapper_Array
  implements Prack_Interface_Enumerable
{
	// TODO: Document!
	public function each( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		foreach ( $this->array as $key => $item )
			call_user_func( $callback, $item );
	}
	
	// TODO: Document!
	public function collect( $callback )
	{
		if ( !is_callable( $callback ) )
			throw new Prack_Error_Callback();
		
		foreach ( $this->array as $key => $item )
			call_user_func( $callback, $key, $item );
	}
}
