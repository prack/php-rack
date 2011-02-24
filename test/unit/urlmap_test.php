<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'support', 'samplemiddleware.php') );

// TODO: Document!
class Prack_URLMapTest extends PHPUnit_Framework_TestCase 
{
	private $domain;
	
	// TODO: Document!
	function setUp()
	{
		$domain = Prack_Builder::domain();
		
		$domain
		  ->map( '/welcome' )
		    ->wherein()
		    ->run( new SampleMiddleware() )
		  ->map( 'https://localhost/secret/area' )
		    ->wherein()
		    ->using( 'SampleMiddleware' )->withArgs()
		    ->run( new SampleMiddleware() )
		  ->map( '/' )
		    ->run( new SampleMiddleware() );
		
		$this->domain_as_middleware_app = $domain->toMiddlewareApp();
	}
	
	/**
	 * instance method remap should transform an indexed array of builders into a lookup table for primitive routing
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_remap_should_transform_an_indexed_array_of_builders_into_a_lookup_table_for_primitive_routing()
	{
		$domain_entries = &$this->domain_as_middleware_app->getEntries();
		
		$this->assertTrue( count( $domain_entries ) == 3 ); // Entry for '/secret/area'
		
		// Do some sanity checks on each of the generated entries.
		foreach ( $domain_entries as $entry )
		{
			$this->assertTrue( is_array( $entry ) );
			$this->assertTrue( count( $entry ) == Prack_URLMap::ENTRY_ELEMENT_COUNT );
		}
	} // instance method remap should transform an indexed array of builders into a lookup table for primitive routing
	
	/**
	 * instance method call should route request appropriately and call the associated middleware
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_call_should_route_request_appropriately_and_call_the_associated_middleware()
	{
		$domain_entries = &$this->domain_as_middleware_app->getEntries();
		
		$env = array(
			'SCRIPT_NAME' => '',
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => '80',
			'HTTP_HOST'   => 'localhost',
			'PATH_INFO'   => '/secret/area/foo/bar'
		);
		
		$middleware_mock = $this->getMock( 'SampleMiddleware', array( 'call' ) );
		$middleware_mock->expects( $this->once() )
		                ->method( 'call' );
		
		$secret_area_entry      = &$domain_entries[ 0 ];
		$request_path           = $secret_area_entry[ 0 ].$secret_area_entry[ 1 ]; // Entry's host concatenated with location
		$secret_area_entry[ 3 ] = $middleware_mock;                                // Mock the app itself
		
		$this->domain_as_middleware_app->call( $env );
	} // instance method call should route request appropriately and call the associated middleware
	
	/**
	 * instance method call should not route request to middleware if host is different
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_call_should_not_route_request_to_middleware_if_host_is_different()
	{
		$domain_entries = &$this->domain_as_middleware_app->getEntries();
		
		$env = array(
			'SCRIPT_NAME' => '',
			'SERVER_NAME' => 'myhost.com',
			'SERVER_PORT' => '80',
			'HTTP_HOST'   => 'someotherhost.org',
			'PATH_INFO'   => '/secret/area/foo/bar'
		);
		
		$middleware_mock = $this->getMock( 'SampleMiddleware', array( 'call' ) );
		$middleware_mock->expects( $this->never() )
		                ->method( 'call' );
		
		$secret_area_entry      = &$domain_entries[ 0 ]; // Entry for '/secret/area'
		$secret_area_entry[ 0 ] = '';                    // Empty the 'host'  field of entry
		$secret_area_entry[ 3 ] = $middleware_mock;      // Mock the middleware_app
		
		$this->domain_as_middleware_app->call( $env );
	} // instance method call should not route request to middleware if host is different
	
	/**
	 * instance method call should route to the site root if it is mounted as a last resort
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_call_should_route_to_the_site_root_if_it_is_mounted_as_a_last_resort()
	{
		$domain_entries = &$this->domain_as_middleware_app->getEntries();
		
		$env = array(
			'SCRIPT_NAME' => '',
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => '80',
			'HTTP_HOST'   => 'localhost',
			'PATH_INFO'   => '/unmapped/place/foo/bar'
		);
		
		$middleware_mock = $this->getMock( 'SampleMiddleware', array( 'call' ) );
		$middleware_mock->expects( $this->once() )
		                ->method( 'call' );
		
		$site_root_entry      = &$domain_entries[ 2 ]; // Entry for '/'
		$site_root_entry[ 3 ] = $middleware_mock;      // Mock the middleware_app
		
		$this->domain_as_middleware_app->call( $env );
	} // instance method call should route to the site root if it is mounted as a last resort
	
	/**
	 * instance method call should revert the environment to the original SCRIPT_NAME and PATH_INFO even if the middleware throws an exception
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_call_should_revert_the_environment_to_the_original_SCRIPT_NAME_and_PATH_INFO_even_if_the_middleware_throws_an_exception()
	{
		$domain_entries = &$this->domain_as_middleware_app->getEntries();
		
		$original_script_name = '';
		$original_path_info   = '/secret/area/throwanexception';
		
		$env = array(
			'SCRIPT_NAME' => $original_script_name,
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => '80',
			'HTTP_HOST'   => 'localhost',
			'PATH_INFO'   => $original_path_info
		);
		
		$middleware_mock = $this->getMock( 'SampleMiddleware', array( 'call' ) );
		$middleware_mock->expects( $this->once() )
		                ->method( 'call' )
		                ->will( $this->throwException( new Exception() ) );
		
		$site_root_entry      = &$domain_entries[ 0 ]; // Entry for '/secret/area'
		$site_root_entry[ 3 ] = $middleware_mock;      // Mock the middleware_app
		
		try
		{
			$this->domain_as_middleware_app->call( $env );
		}
		catch ( Exception $e )
		{
			// Don't need to do anything here.
		}
		
		$this->assertEquals( $original_script_name, $env[ 'SCRIPT_NAME' ] );
		$this->assertEquals( $original_path_info  , $env[ 'PATH_INFO' ]   );
	} // instance method call should revert the environment to the original SCRIPT_NAME and PATH_INFO even if the middleware throws an exception
}