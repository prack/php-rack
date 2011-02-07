<?php

require_once join( DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'support', 'samplemiddleware.php') );

class Prack_BuilderTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * Root instance returned by Builder::build() should have null parent
	 * @author Joshua Morris
	 * @test
	 */
	public function Root_instance_returned_by_Builder_build_should_have_null_parent()
	{
		$app = Prack_Builder::build();
		$this->assertTrue( $app->getContext() === null );
	} // Root instance returned by Builder::build() should have null parent
	
	/**
	 * Root instance returned by Builder::build() should have an empty array of middleware
	 * @author Joshua Morris
	 * @test
	 */
	public function Root_instance_returned_by_Builder_build_should_have_an_empty_array_of_middleware()
	{
		$app        = Prack_Builder::build();
		$middleware = $app->getMiddleware();
		$this->assertTrue( is_array($middleware));
		$this->assertTrue( empty($middleware) );
	} // Root instance returned by Builder::build() should have an empty array of middleware
	
	/**
	 * Instance method using should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_using_should_return_the_builder_itself()
	{
		$app = Prack_Builder::build();
		$this->assertTrue( $app->using( 'ExampleMiddleware' ) === $app, 'message' );
	} // Instance method using should return the builder itself for fluent interface
	
	/**
	 * Instance method using should modify the internal $mw_class attribute of the builder
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_using_should_modify_the_internal_mw_class_attribute_of_the_builder()
	{
		$app = Prack_Builder::build();
		$this->assertNull( $app->getStateMiddlewareClass() );
		$app->using( 'ExampleMiddleware' );
		$this->assertSame( $app->getStateMiddlewareClass(), 'ExampleMiddleware' );
	} // Instance method using should modify the internal $mw_class attribute of the builder
	
	/**
	 * Instance method withArgs should throw an exception if no middleware class has been specified
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_throw_an_exception_if_no_middleware_class_has_been_specified()
	{
		$app = Prack_Builder::build();
		$this->setExpectedException( 'Prack_Error_FluentInterfacePreconditionFailed' );
		$app->withArgs();
	} // Instance method withArgs should throw an exception if no middleware class has been specified
	
	/**
	 * Instance method withArgs should return the builder itself for fluent interface
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_return_the_builder_itself_for_fluent_interface()
	{
		$app = Prack_Builder::build();
		$app->using( 'SampleMiddleware' );
		$this->assertSame( $app->withArgs('foo', 'bar'), $app );
	} // Instance method withArgs should return the builder itself for fluent interface
	
	/**
	 * Instance method withArgs should add an instance of the middleware to the current builder's stack
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_withArgs_should_add_an_instance_of_the_middleware_to_the_current_builders_stack()
	{
		$app = Prack_Builder::build();
		$app->using('SampleMiddleware')
		    ->withArgs('foo', 'bar');
		
		// Fetch the middleware stack.
		$middleware = $app->getMiddleware();
		
		$this->assertEquals( count( $middleware ), 1, 'message');
		
	} // Instance method withArgs should add an instance of the middleware to the current builder's stack
	
	/**
	 * Instance method run should return the builder's context, i.e. its parent builder
	 * @author Joshua Morris
	 * @test
	 */
	public function Instance_method_run_should_return_the_builders_context()
	{
		$app = Prack_Builder::build();
		$this->assertSame( $app->run(), $app->getContext() );
	} // Instance method run should return the builder's context
}