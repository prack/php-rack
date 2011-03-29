<?php

// TODO: Document!
class Prack_RuntimeTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It sets X-Runtime if none is set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_sets_X_Runtime_if_none_is_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), 'Hello, World!' );
		
		$env      = array();
		$response = Prack_Runtime::with( $middleware_app )->call( $env );
		
		$this->assertRegExp( '/[\d\.]+/', (string)@$response[ 1 ][ 'X-Runtime' ] );
	} // It sets X-Runtime if none is set
	
	/**
	 * It doesn't set the X-Runtime if it is already set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_doesn_t_set_the_X_Runtime_if_it_is_already_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  200, array( 'Content-Type' => 'text/plain', 'X-Runtime' => 'foobar' ), 'Hello, World!' );
		
		$env      = array();
		$response = Prack_Runtime::with( $middleware_app )->call( $env );
		
		$this->assertEquals( 'foobar', (string)@$response[ 1 ][ 'X-Runtime' ] );
	} // It doesn't set the X-Runtime if it is already set
	
	/**
	 * It should allow a suffix to be set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_a_suffix_to_be_set()
	{
		$middleware_app = new Prack_Test_Echo( 200, array( 'Content-Type' => 'text/plain' ), 'Hello, World!' );
		
		$env      = array();
		$response = Prack_Runtime::with( $middleware_app, 'Test' )->call( $env );
		
		$this->assertRegExp( '/[\d\.]+/', (string)@$response[ 1 ][ 'X-Runtime-Test' ] );
	} // It should allow a suffix to be set
	
	/**
	 * It should allow multiple timers to be set
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_allow_multiple_timers_to_be_set()
	{
		$middleware_app = new Prack_Test_Echo(
		  200, array( 'Content-Type' => 'text/plain' ), 'Hello, World!', 'sleep( 0.025 );' );
		
		$runtime = Prack_Runtime::with( $middleware_app, 'App' );
		for ( $i = 0; $i < 50; $i++ )
			$runtime = Prack_Runtime::with( $runtime, (string)$i );
		
		$runtime  = Prack_Runtime::with( $runtime, "All" );
		$env      = array();
		$response = $runtime->call( $env );
		
		$this->assertRegExp( '/[\d\.]+/', (string)@$response[ 1 ][ 'X-Runtime-App' ] );
		$this->assertRegExp( '/[\d\.]+/', (string)@$response[ 1 ][ 'X-Runtime-All' ] );
		
		$this->assertGreaterThan(
			(float)@$response[ 1 ][ 'X-Runtime-App' ],
			(float)@$response[ 1 ][ 'X-Runtime-All' ]
		);
		
	} // It should allow multiple timers to be set
}