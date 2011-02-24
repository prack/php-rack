<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', 'support', 'samplemiddleware.php') );

// TODO: Document!
class Prack_BuilderTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * root instance returned by static method build should have null parent
	 * @author Joshua Morris
	 * @test
	 */
	public function root_instance_returned_by_static_method_domain_should_have_null_parent()
	{
		$domain = Prack_Builder::domain();
		$this->assertTrue( $domain->getParent() === null );
	} // root instance returned by static method build should have null parent
	
	/**
	 * root instance returned by static method build should have an empty array of middleware
	 * @author Joshua Morris
	 * @test
	 */
	public function root_instance_returned_by_static_method_domain_should_have_an_empty_array_of_middleware()
	{
		$domain     = Prack_Builder::domain();
		$middleware = $domain->getMiddlewareStack();
		$this->assertTrue( is_array( $middleware ) );
		$this->assertTrue( empty( $middleware ) );
	} // root instance returned by static method build should have an empty array of middleware
	
	/**
	 * root instance returned by static method build should have an empty array of mapped builders
	 * @author Joshua Morris
	 * @test
	 */
	public function root_instance_returned_by_static_method_domain_should_have_an_empty_array_of_children()
	{
		$domain   = Prack_Builder::domain();
		$children = $domain->getEndpoint();
		$this->assertTrue( empty( $children ) );
	} // root instance returned by static method build should have an empty array of mapped builders
	
	/**
	 * instance method using should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_using_should_return_the_builder_itself()
	{
		$domain = Prack_Builder::domain();
		$this->assertTrue( $domain->using( 'ExampleMiddleware' ) === $domain, 'message' );
	} // instance method using should return the builder itself for fluent interface
	
	/**
	 * instance method using should modify the internal $mw_class attribute of the builder
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_using_should_modify_the_internal_mw_class_attribute_of_the_builder()
	{
		$domain = Prack_Builder::domain();
		$this->assertNull( $domain->getFIUsingClass() );
		$domain->using( 'ExampleMiddleware' );
		$this->assertSame( $domain->getFIUsingClass(), 'ExampleMiddleware' );
	} // instance method using should modify the internal $mw_class attribute of the builder
	
	/**
	 * instance method withArgs should throw an exception if no middleware class has been specified
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_withArgs_should_throw_an_exception_if_no_middleware_class_has_been_specified()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_FluentInterfacePreconditionNotMet' );
		$domain->withArgs();
	} // instance method withArgs should throw an exception if no middleware class has been specified
	
	/**
	 * instance method withArgs should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_withArgs_should_return_the_builder_itself_for_fluent_interface()
	{
		$domain = Prack_Builder::domain();
		$domain->using( 'SampleMiddleware' );
		$this->assertSame( $domain->withArgs( 'foo', 'bar' ), $domain );
	} // instance method withArgs should return the builder itself for fluent interface
	
	/**
	 * instance method withArgs should add an instance of the middleware to the current builder's stack
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_withArgs_should_add_an_instance_of_the_middleware_to_the_current_builders_stack()
	{
		$domain = Prack_Builder::domain();
		$domain->using( 'SampleMiddleware' )
		       ->withArgs( 'foo', 'bar' );
		
		// Fetch the middleware stack.
		$middleware = $domain->getMiddlewareStack();
		
		$this->assertEquals( reset( $middleware ), array( 'SampleMiddleware', array( 'foo', 'bar' ) ) );
	} // instance method withArgs should add an instance of the middleware to the current builder's stack
	
	/**
	 * instance method run should throw an exception if invoked twice
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_run_should_throw_an_exception_if_invoked_twice()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_ShallowEndpointRedeclared' );
		
		$domain->run( new SampleMiddleware() );
		$domain->run( new SampleMiddleware() );
	} // instance method run should throw an exception if invoked twice
	
	/**
	 * instance method run should throw an exception if any mapped builders are specified
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_run_should_throw_an_exception_if_any_children_are_specified()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_BothMapAndRunDeclaredAtSameLevel' );
		$domain->map( '/admin' )
		         ->run( new SampleMiddleware() )
		       ->run( new SampleMiddleware() );
	} // instance method run should throw an exception if any mapped builders are specified
	
	/**
	 * instance method run should append the provided middleware to the end of the stack and disable mapping of children
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_run_should_append_the_provided_middleware_to_the_end_of_the_stack_and_disable_mapping_of_children()
	{
		$domain = Prack_Builder::domain();
		$domain->run( new SampleMiddleware() );
		$this->assertTrue( $domain->isShallow() );
	} // instance method run should append the provided middleware to the end of the stack and disable mapping of children
	
	/**
	 * instance method run should return the builder's parent
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_run_should_return_the_builders_parent()
	{
		$domain = Prack_Builder::domain();
		$this->assertSame( $domain->run( new SampleMiddleware() ), $domain->getParent() );
	} // instance method run should return the builder's context
	
	/**
	 * instance method map should create a new chlld instance of the builder class to nest middleware within
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_map_should_return_a_new_builder_class_to_nest_middleware_within()
	{
		$domain                     = Prack_Builder::domain();
		$builder_for_admin_location = $domain->map( '/admin' );
		$children                   = $domain->getEndpoint();
		$this->assertTrue( in_array( $builder_for_admin_location, $children ) );
	} // instance method map should return a new chlld builder to nest middleware within
	
	/**
	 * instance method map should throw an exception on apparently duplicate mappings
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_map_should_throw_an_exception_on_apparently_duplicate_mappings()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_DuplicateMapping' );
		
		$domain->map( '/tweets' );
		$domain->map( '/tweets' );
	} // instance method map should throw an exception on apparently duplicate mappings
	
	/**
	 * instance method wherein should simply return the builder on which it was called
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_wherein_should_simply_return_the_builder_on_which_it_was_called()
	{
		$domain = Prack_Builder::domain();
		$domain = $domain->using( 'SampleMiddleware' )
		                 ->map( '/someurl' );
		
		$this->assertSame( $domain, $domain->wherein(), 'message' );
	} // instance method wherein should simply return the builder on which it was called
	
	/**
	 * instance method toMiddlewareApp should return an object which confirms to Prack_IMiddleware interface
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_toMiddlewareApp_should_return_an_object_which_confirms_to_Prack_IMiddleware_interface()
	{
		$domain = Prack_Builder::domain();
		
		$middleware_app = new SampleMiddleware();
		$domain->map( '/admin' )
		         ->map( '/secret' )
		           ->run( $middleware_app );
		
		$this->assertTrue( $domain->toMiddlewareApp() instanceof Prack_Interface_MiddlewareApp );
	} // instance method toMiddlewareApp should return an object which confirms to Prack_IMiddleware interface
	
	/**
	 * instance method toMiddlewareApp should throw an exception if neither map nor run has been called
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_toMiddlewareApp_should_throw_an_exception_if_neither_map_nor_run_has_been_called()
	{
		$domain = Prack_Builder::domain();
		$this->setExpectedException( 'Prack_Error_Builder_NoMiddlewareSpecified' );
		$domain->toMiddlewareApp();
	} // instance method toMiddlewareApp should throw an exception if neither map nor run has been called
	
	/**
	 * instance method toArray should throw an exception if the resource location starts with a non-slash character
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_toArray_should_throw_an_exception_if_the_resource_location_starts_with_a_non_slash_character()
	{
		$domain = Prack_Builder::domain();
		
		$this->setExpectedException( 'Prack_Error_Builder_ResourceLocationInvalid' );
		
		$builder = $domain->map( 'admin' );
		$builder->toArray();
	} // instance method toArray should throw an exception if the resource location starts with a non-slash character
	
	/**
	 * instance method toArray should extract host and location properly if provided in the location
	 * @author Joshua Morris
	 * @test
	 */
	public function instance_method_toArray_should_extract_host_and_location_properly_if_provided_in_the_location()
	{
		$domain = Prack_Builder::domain();
		
		$host     = 'myhub.org';
		$location = '/admin';
		$url      = "https://{$host}{$location}";
		
		$builder = $domain->map( 'https://myhub.org/admin' );
		$builder->run( new SampleMiddleware() );
		
		$entry = $builder->toArray();
		$this->assertEquals( $host, $entry[ 0 ] );
		$this->assertEquals( $location, $entry[ 1 ] );
	} // instance method toArray should extract host and location properly if provided in the location
}