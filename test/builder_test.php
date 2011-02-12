<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'support', 'samplemiddleware.php') );

class Prack_BuilderTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * Root instance returned by static method build should have null parent
	 * @author Joshua Morris
	 * @test
	 */
	public function Root_instance_returned_by_static_method_domain_should_have_null_parent()
	{
		$domain = Prack_Builder::domain();
		$this->assertTrue( $domain->getParent() === null );
	} // Root instance returned by static method build should have null parent
	
	
	/**
	 * Root instance returned by static method build should have an empty array of middleware
	 * @author Joshua Morris
	 * @test
	 */
	public function Root_instance_returned_by_static_method_domain_should_have_an_empty_array_of_middleware()
	{
		$domain     = Prack_Builder::domain();
		$middleware = $domain->getMiddlewareStack();
		$this->assertTrue( is_array( $middleware ) );
		$this->assertTrue( empty( $middleware ) );
	} // Root instance returned by static method build should have an empty array of middleware
	
	
	/**
	 * Root instance returned by static method build should have an empty array of mapped builders
	 * @author Joshua Morris
	 * @test
	 */
	public function Root_instance_returned_by_static_method_domain_should_have_an_empty_array_of_children()
	{
		$domain   = Prack_Builder::domain();
		$children = $domain->getEndpoint();
		$this->assertTrue( empty( $children ) );
	} // Root instance returned by static method build should have an empty array of mapped builders
	
	
	/**
	 * Instance method using should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_using_should_return_the_builder_itself()
	{
		$domain = Prack_Builder::domain();
		$this->assertTrue( $domain->using( 'ExampleMiddleware' ) === $domain, 'message' );
	} // Instance method using should return the builder itself for fluent interface
	
	
	/**
	 * Instance method using should modify the internal $mw_class attribute of the builder
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_using_should_modify_the_internal_mw_class_attribute_of_the_builder()
	{
		$domain = Prack_Builder::domain();
		$this->assertNull( $domain->getFIUsingClass() );
		$domain->using( 'ExampleMiddleware' );
		$this->assertSame( $domain->getFIUsingClass(), 'ExampleMiddleware' );
	} // Instance method using should modify the internal $mw_class attribute of the builder
	
	
	/**
	 * Instance method withArgs should throw an exception if no middleware class has been specified
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_throw_an_exception_if_no_middleware_class_has_been_specified()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_FluentInterfacePreconditionNotMet' );
		$domain->withArgs();
	} // Instance method withArgs should throw an exception if no middleware class has been specified
	
	
	/**
	 * Instance method withArgs should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_return_the_builder_itself_for_fluent_interface()
	{
		$domain = Prack_Builder::domain();
		$domain->using( 'SampleMiddleware' );
		$this->assertSame( $domain->withArgs( 'foo', 'bar' ), $domain );
	} // Instance method withArgs should return the builder itself for fluent interface
	
	
	/**
	 * Instance method withArgs should add an instance of the middleware to the current builder's stack
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_add_an_instance_of_the_middleware_to_the_current_builders_stack()
	{
		$domain = Prack_Builder::domain();
		$domain->using( 'SampleMiddleware' )
		       ->withArgs( 'foo', 'bar' );
		
		// Fetch the middleware stack.
		$middleware = $domain->getMiddlewareStack();
		
		$this->assertEquals( reset( $middleware ), array( 'SampleMiddleware', array( 'foo', 'bar' ) ) );
	} // Instance method withArgs should add an instance of the middleware to the current builder's stack
	
	
	/**
	 * Instance method run should throw an exception if invoked twice
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_run_should_throw_an_exception_if_invoked_twice()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_ShallowEndpointRedeclared' );
		
		$domain->run( new SampleMiddleware() );
		$domain->run( new SampleMiddleware() );
	} // Instance method run should throw an exception if invoked twice
	
	/**
	 * Instance method run should throw an exception if any mapped builders are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_run_should_throw_an_exception_if_any_children_are_specified()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_BothMapAndRunDeclaredAtSameLevel' );
		$domain->map( '/admin' )
		         ->run( new SampleMiddleware() )
		       ->run( new SampleMiddleware() );
	} // Instance method run should throw an exception if any mapped builders are specified
	
	/**
	 * Instance method run should append the provided middleware to the end of the stack and disable mapping of children
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_run_should_append_the_provided_middleware_to_the_end_of_the_stack_and_disable_mapping_of_children()
	{
		$domain = Prack_Builder::domain();
		$domain->run( new SampleMiddleware() );
		$this->assertTrue( $domain->isShallow() );
	} // Instance method run should append the provided middleware to the end of the stack and disable mapping of children
	
	/**
	 * Instance method run should return the builder's parent
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_run_should_return_the_builders_parent()
	{
		$domain = Prack_Builder::domain();
		$this->assertSame( $domain->run( new SampleMiddleware() ), $domain->getParent() );
	} // Instance method run should return the builder's context
	
	
	/**
	 * Instance method map should create a new chlld instance of the builder class to nest middleware within
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_map_should_return_a_new_builder_class_to_nest_middleware_within()
	{
		$domain                     = Prack_Builder::domain();
		$builder_for_admin_location = $domain->map( '/admin' );
		$children                   = $domain->getEndpoint();
		$this->assertTrue( in_array( $builder_for_admin_location, $children ) );
	} // Instance method map should return a new chlld builder to nest middleware within
	
	
	/**
	 * Instance method map should throw an exception on apparently duplicate mappings
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_map_should_throw_an_exception_on_apparently_duplicate_mappings()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_DuplicateMapping' );
		
		$domain->map( '/tweets' );
		$domain->map( '/tweets' );
	} // Instance method map should throw an exception on apparently duplicate mappings
	
	
	/**
	 * Instance method wherein should simply return the builder on which it was called
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_wherein_should_simply_return_the_builder_on_which_it_was_called()
	{
		$domain = Prack_Builder::domain();
		$domain = $domain->using( 'SampleMiddleware' )
		                 ->map( '/someurl' );
		
		$this->assertSame( $domain, $domain->wherein(), 'message' );
	} // Instance method wherein should simply return the builder on which it was called
	
	/**
	 * Instance method toMiddlewareApp should return an object which confirms to Prack_IMiddleware interface
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_toMiddlewareApp_should_return_an_object_which_confirms_to_Prack_IMiddleware_interface()
	{
		$domain = Prack_Builder::domain();
		
		$middleware_app = new SampleMiddleware();
		$domain->map( '/admin' )
		         ->map( '/secret' )
		           ->run( $middleware_app );
		
		$this->assertTrue( $domain->toMiddlewareApp() instanceof Prack_IMiddlewareApp );
	} // Instance method toMiddlewareApp should return an object which confirms to Prack_IMiddleware interface
	
	
	/**
	 * Instance method toMiddlewareApp should throw an exception if neither map nor run has been called
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_toMiddlewareApp_should_throw_an_exception_if_neither_map_nor_run_has_been_called()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_NoMiddlewareSpecified' );
		$domain->toMiddlewareApp();
	} // Instance method toMiddlewareApp should throw an exception if neither map nor run has been called
	
	/**
	 * Instance method toArray should throw an exception if the resource location starts with a non-slash character
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_toArray_should_throw_an_exception_if_the_resource_location_starts_with_a_non_slash_character()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_ResourceLocationInvalid' );
		
		$builder = $domain->map( 'admin' );
		$builder->toArray();
	} // Instance method toArray should throw an exception if the resource location starts with a non-slash character
	
	/**
	 * Instance method toArray should extract host and location properly if provided in the location
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_toArray_should_extract_host_and_location_properly_if_provided_in_the_location()
	{
		$domain = Prack_Builder::domain();
		
		$host     = 'myhub.org';
		$location = '/admin';
		$url      = "https://{$host}{$location}";
		
		$builder = $domain->map( 'https://myhub.org/admin' );
		$builder->run( new SampleMiddleware() );
		
		$entry = $builder->toArray();
		$this->assertEquals( $host, $entry[0] );
		$this->assertEquals( $location, $entry[1] );
	} // Instance method toArray should extract host and location properly if provided in the location
	
}