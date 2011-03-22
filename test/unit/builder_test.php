<?php

// TODO: Document!
class Prack_Test_NothingMiddleware
  implements Prack_I_MiddlewareApp
{
	static public $env;
	
	private $middleware_app;
	
	// TODO: Document!
	static function env()
	{
		return Prack_Test_NothingMiddleware::$env;
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $eval_on_call = null )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		Prack_Test_NothingMiddleware::$env = $env;
		$response = $this->middleware_app->call( $env );
		return $response;
	}
}

// TODO: Document!
class Prack_Test_AppClass
  implements Prack_I_MiddlewareApp
{
	private $called;
	
	// TODO: Document!
	function __construct()
	{
		$this->called = 0;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		if ( $this->called > 0 )
			throw new Exception( 'bzzzt' );
		
		$this->called++;
		
		return Prb::Ary( array(
			Prb::Num( 200 ),
			Prb::Hsh( array( 'Content-Type' => Prb::Str( 'text/plain' ) ) ),
			Prb::Ary( array( Prb::Str( 'OK' ) ) )
		) );
	}
}

class Prack_BuilderTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It supports mapping
	 * @author Joshua Morris
	 * @test
	 */
	public function It_supports_mapping()
	{
		$middleware_app = new Prack_Builder();
		$middleware_app
		  ->map( '/' )
		    ->run(
		      new Prack_Test_Echo(
		        Prb::Num( 200 ),
		        Prb::Hsh(),
		        Prb::Ary( array( Prb::Str( 'root' ) ) )
		      )
		    )
		  ->map( '/sub' )
		    ->run(
		      new Prack_Test_Echo(
		        Prb::Num( 200 ),
		        Prb::Hsh(),
		        Prb::Ary( array( Prb::Str( 'sub' ) ) )
		      )
		    )
		->toMiddlewareApp();
		
		$this->assertEquals(
		  'root', Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->getBody()->toS()->raw()
		);
		
		$this->assertEquals(
		  'sub', Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/sub' ) )->getBody()->toS()->raw()
		);
	} // It supports mapping
	
	/**
	 * It doesn't dupe env even when mapping
	 * @author Joshua Morris
	 * @test
	 */
	public function It_doesn_t_dupe_env_even_when_mapping()
	{
		$middleware_app = new Prack_Builder();
		$middleware_app
		  ->using( 'Prack_Test_NothingMiddleware' )->build()
		  ->map( '/' )
		    ->run(
		      new Prack_Test_Echo(
		        Prb::Num( 200 ), Prb::Hsh(), Prb::Ary( array( Prb::Str( 'root' ) ) ),
		        '$env->set( "new_key", Prb::Str( "new_value" ) );'
		      )
		    )
		->toMiddlewareApp();
		
		$this->assertEquals(
		  'root', Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->getBody()->toS()->raw()
		);
		
		$this->assertEquals( Prb::Str( 'new_value' ),
		                     Prack_Test_NothingMiddleware::env()->get( 'new_key' ) );
	} // It doesn't dupe env even when mapping
	
	/**
	 * It chains apps by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_chains_apps_by_default()
	{
		$middleware_app = new Prack_Builder();
		$middleware_app
		  ->using( 'Prack_ShowExceptions' )->build()
		  ->run(
		    new Prack_Test_Echo(
		      Prb::Num( 200 ), Prb::Hsh(), Prb::Ary( array( Prb::Str( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  )
		->toMiddlewareApp();
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
	} // It chains apps by default
	
	/**
	 * It has implicit toMiddlewareApp
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_implicit_toMiddlewareApp()
	{
		$middleware_app = new Prack_Builder();
		$middleware_app
		  ->using( 'Prack_ShowExceptions' )->build()
		  ->run(
		    new Prack_Test_Echo(
		      Prb::Num( 200 ), Prb::Hsh(), Prb::Ary( array( Prb::Str( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  );
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
	} // It has implicit toMiddlewareApp
	
	/**
	 * It supports callbacks on use
	 * @author Joshua Morris
	 * @test
	 */
	public function It_supports_callbacks_on_use()
	{
		$callback       = array( $this, 'onBuild' );
		$middleware_app = Prack_Builder::domain( $callback );
		
		$response = Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) );
		$this->assertTrue( $response->isClientError() );
		$this->assertEquals( 401, $response->getStatus()->raw() );
		
		# with auth...
		$response = Prack_Mock_Request::with( $middleware_app )->get(
		  Prb::Str( '/' ),
		  Prb::Hsh( array(
		    'HTTP_AUTHORIZATION' => Prb::Str( 'Basic ' )->concat( Prb::Str( 'joe:secret' )->base64Encode() )
		  ) )
		);
		
		$this->assertEquals( 200, $response->getStatus()->raw() );
		$this->assertEquals( 'Hi Boss', $response->getBody()->toS()->raw() );
	} // It supports callbacks on use
	
	// TODO: Document!
	public function onBuild( $builder )
	{
		$callback = create_function( '$username,$password', 'return "secret" == $password->raw();' );
		$builder
		  ->using( 'Prack_ShowExceptions' )->build()
		  ->using( 'Prack_Auth_Basic' )->withArgs( null )->withCallback( $callback )->build()
		  ->run(
		      new Prack_Test_Echo(
		        Prb::Num( 200 ),
		        Prb::Hsh(),
		        Prb::Ary( array( Prb::Str( 'Hi Boss' ) ) )
		      )
		    )
		->toMiddlewareApp(); // Just for style, here. Optional.
	}
	
	/**
	 * It has explicit toMiddlewareApp
	 * @author Joshua Morris
	 * @test
	 */
	public function It_has_explicit_toMiddlewareApp()
	{
		$middleware_app = Prack_Builder::domain()
		  ->using( 'Prack_ShowExceptions' )->build()
		  ->run(
		    new Prack_Test_Echo(
		      Prb::Num( 200 ), Prb::Hsh(), Prb::Ary( array( Prb::Str( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  )
		->toMiddlewareApp();
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
	} // It has explicit toMiddlewareApp
	
	/**
	 * It should initialize apps once
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_initialize_apps_once()
	{
		$middleware_app = new Prack_Builder();
		$middleware_app
		  ->using( 'Prack_ShowExceptions' )->build()
		  ->run( new Prack_Test_AppClass() );
		
		$this->assertEquals( Prb::Num( 200 ), Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->getStatus() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prb::Str( '/' ) )->isServerError() );
	} // It should initialize apps once
	
	/**
	 * It should throw an exception if the fluent interface is misused
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_the_fluent_interface_is_misused()
	{
		try
		{
			Prack_Builder::domain()
			  ->build();
		} catch ( Exception $e1 ) {}
		
		if ( isset( $e1 ) )
			$this->assertRegExp( '/provide the middleware app class with using/', $e1->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app class not provided.' );
		
		try
		{
			Prack_Builder::domain()
			  ->using( 'Prack_Test_Echo' )->withCallback( array( $this, 'nonexistantFunction' ) )->build();
		} catch ( Exception $e2 ) {}
		
		if ( isset( $e2 ) )
			$this->assertRegExp( '/is not actually callable/', $e2->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app callback is not actually callable.' );
		
		try
		{
			// must conclude middleware app specification with a call to build:
			Prack_Builder::domain()
			  ->via( 'Prack_Test_Echo' )->withArgs()->andCallback( array( $this, 'nonexistantFunction' ) )
			  ->using( 'Prack_Test_Echo' )->withCallback( array( $this, 'nonexistantFunction' ) );
		} catch ( Exception $e3 ) {}
		
		if ( isset( $e3 ) )
			$this->assertRegExp( '/until previous is fully specified/', $e3->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app specification is incomplete.' );
	} // It should throw an exception if the fluent interface is misused
	
	/**
	 * It should throw an exception if the callback provided to a new builder isn't actually callable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_the_callback_provided_to_a_new_builder_isn_t_actually_callable()
	{
		try
		{
			Prack_Builder::domain( array( $this, 'nonexistantFunction' ) );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertRegExp( '/is not actually callable/', $e4->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app callback is not actually callable.' );
	} // It should throw an exception if the callback provided to a new builder isn't actually callable
	
	/**
	 * It should throw an exception if run is called in the middle of a middleware app specification
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_run_is_called_in_the_middle_of_a_middleware_app_specification()
	{
		try
		{
			// must conclude middleware app specification with a call to build:
			Prack_Builder::domain()
			  ->via( 'Prack_Test_Echo' )->withArgs()->andCallback( array( $this, 'nonexistantFunction' ) )
			  ->run( new Prack_Test_Echo() );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertRegExp( '/until previous is fully specified/', $e4->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app specification is incomplete.' );
	} // It should throw an exception if run is called in the middle of a middleware app specification
	
	/**
	 * It should throw an exception if run is called with other than Prack_I_MiddlewareApp
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_run_is_called_with_other_than_Prack_I_MiddlewareApp()
	{
		try
		{
			// must conclude middleware app specification with a call to build:
			Prack_Builder::domain()
			  ->run( new stdClass() );
		} catch ( Exception $e5 ) {}
		
		if ( isset( $e5 ) )
			$this->assertRegExp( '/must be an instance of Prack_I_MiddlewareApp/', $e5->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app specification is incomplete.' );
	} // It should throw an exception if run is called with other than Prack_I_MiddlewareApp
	
}