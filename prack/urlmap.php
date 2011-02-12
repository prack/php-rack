<?php

class Prack_URLMap implements Prack_IMiddlewareApp
{
	const ENTRY_ELEMENT_COUNT = 4;
	
	private $entries;
	
	function __construct( $builders )
	{
		$this->remap( $builders );
	}
	
	public function remap( $builders )
	{
		$this->entries = array();
		
		foreach ( $builders as $builder )
			array_push( $this->entries, $builder->toArray() );
		
		$comparison_function = create_function('$a,$b', 'return strlen($b[0].$b[1]) - strlen($a[0].$a[1]);');
		usort( $this->entries, $comparison_function );
		
		return $this->entries;
	}
	
	
	// This is where requests get routed.
	public function call(&$env)
	{
		// #TODO: Implement primitive entry-based routing.
	}
	
	
	public function getEntries()
	{
		return $this->entries;
	}
}