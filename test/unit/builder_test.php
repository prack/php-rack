<?php

// TODO: Document!
class Prack_Test_NothingMiddleware
  implements Prack_Interface_MiddlewareApp
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
  implements Prack_Interface_MiddlewareApp
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
		
		return array(
			200,
			Prack::_Hash( array( 'Content-Type' => Prack::_String( 'text/plain' ) ) ),
			Prack::_Array( array( Prack::_String( 'OK' ) ) )
		);
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
		        200,
		        Prack::_Hash(),
		        Prack::_Array( array( Prack::_String( 'root' ) ) )
		      )
		    )
		  ->map( '/sub' )
		    ->run(
		      new Prack_Test_Echo(
		        200,
		        Prack::_Hash(),
		        Prack::_Array( array( Prack::_String( 'sub' ) ) )
		      )
		    )
		->toMiddlewareApp();
		
		$this->assertEquals(
		  'root', Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->getBody()->toS()->toN()
		);
		
		$this->assertEquals(
		  'sub', Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/sub' ) )->getBody()->toS()->toN()
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
		        200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'root' ) ) ),
		        '$env->set( "new_key", Prack::_String( "new_value" ) );'
		      )
		    )
		->toMiddlewareApp();
		
		$this->assertEquals(
		  'root', Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->getBody()->toS()->toN()
		);
		
		$this->assertEquals( Prack::_String( 'new_value' ),
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
		      200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  )
		->toMiddlewareApp();
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
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
		      200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  );
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
	} // It has implicit toMiddlewareApp
	
	/**
	 * It supports callbacks on use
	 * @author Joshua Morris
	 * @test
	 */
	public function It_supports_callbacks_on_use()
	{
		// it "supports blocks on use" do
		//   app = Rack::Builder.new do
		//     use Rack::ShowExceptions
		//     use Rack::Auth::Basic do |username, password|
		//       'secret' == password
		//     end
		// 
		//     run lambda { |env| [200, {}, ['Hi Boss']] }
		//   end
		// 
		//   response = Rack::MockRequest.new(app).get("/")
		//   response.should.be.client_error
		//   response.status.should.equal 401
		// 
		//   # with auth...
		//   response = Rack::MockRequest.new(app).get("/",
		//       'HTTP_AUTHORIZATION' => 'Basic ' + ["joe:secret"].pack("m*"))
		//   response.status.should.equal 200
		//   response.body.to_s.should.equal 'Hi Boss'
		// end
		$this->markTestSkipped( 'pending HTTP Basic Auth implementation' );
	} // It supports callbacks on use
	
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
		      200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'root' ) ) ),
		      'throw new Exception( "bzzzt" );'
		    )
		  )
		->toMiddlewareApp();
		
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
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
		
		$this->assertEquals( 200, Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->getStatus() );
		$this->assertTrue( Prack_Mock_Request::with( $middleware_app )->get( Prack::_String( '/' ) )->isServerError() );
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
	 * It should throw an exception if run is called with other than Prack_Interface_MiddlewareApp
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_throw_an_exception_if_run_is_called_with_other_than_Prack_Interface_MiddlewareApp()
	{
		try
		{
			// must conclude middleware app specification with a call to build:
			Prack_Builder::domain()
			  ->run( new stdClass() );
		} catch ( Exception $e5 ) {}
		
		if ( isset( $e5 ) )
			$this->assertRegExp( '/must be an instance of Prack_Interface_MiddlewareApp/', $e5->getMessage() );
		else
			$this->fail( 'Expected exception when middleware app specification is incomplete.' );
	} // It should throw an exception if run is called with other than Prack_Interface_MiddlewareApp
	
}