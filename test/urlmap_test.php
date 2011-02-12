<?php

class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * Instance method remap should transform an indexed array of builders into a lookup table for primitive routing
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_remap_should_transform_an_indexed_array_of_builders_into_a_lookup_table_for_primitive_routing()
	{
		$domain = $this->constructTestDomain( Prack_Builder::domain() );
		
		$url_map = new Prack_URLMap( $domain->getEndpoint() );
		$entries = $url_map->getEntries();
		
		// The URLMap should produce 3 entries, one for each mapped builder.
		$this->assertTrue( count( $entries ) == 3 );
		
		// Do some sanity checks on each of the generated entries.
		foreach ( $entries as $entry )
		{
			$this->assertTrue( is_array( $entry ) );
			$this->assertTrue( count($entry) == Prack_URLMap::ENTRY_ELEMENT_COUNT );
		}
	} // Instance method remap should transform an indexed array of builders into a lookup table for primitive routing
	
	
	private function constructTestDomain( $domain )
	{
		$domain
		  ->map( '/' )
		    ->run( new SampleMiddleware() )
		  ->map( '/secret/area' )
		    ->wherein()
		    ->using( 'SampleMiddleware' )->withArgs()
		    ->run( new SampleMiddleware() )
		  ->map( '/welcome' )
		    ->wherein()
		    ->run( new SampleMiddleware() );
		
		return $domain;
	}
}