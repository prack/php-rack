<?php

// TODO: Document!
class Prack_Wrapper_String
  implements Prack_Interface_LengthAware, Prack_Interface_Stringable
{
	private $string;
	
	// TODO: Document!
	static function with( $string )
	{
		return new Prack_Wrapper_String( $string );
	}
	
	// TODO: Document!
	function __construct( $string = '' )
	{
		$this->string = $string;
	}
	
	// TODO: Document!
	public function __toString()
	{
		return $this->string;
	}
	
	// TODO: Document!
	public function length()
	{
		return strlen( $this->string );
	}
	
	// TODO: Document!
	public function isEmpty()
	{
		return ( strlen( $this->string ) == 0 );
	}
	
	// TODO: Document!
	public function contains( $substring )
	{
		return ( strpos( $this->string, $substring ) !== false );
	}
}