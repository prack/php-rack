<?php

// TODO: Document!
class Prack_BuilderTest_NothingMiddleware
  implements Prack_Interface_MiddlewareApp
{
	static public $env;
	
	private $middleware_app;
	
	// TODO: Document!
	static function env()
	{
		return Prack_BuilderTest_NothingMiddleware::$env;
	}
	
	// TODO: Document!
	function __construct( $middleware_app, $eval_on_call = null )
	{
		$this->middleware_app = $middleware_app;
	}
	
	// TODO: Document!
	public function call( $env )
	{
		Prack_BuilderTest_NothingMiddleware::$env = $env;
		$response = $this->middleware_app->call( $env );
		return $response;
	}
}

class Prack_BuilderTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It supports mapping
	 * @author Joshua Morris
	 * @test
	 */
	/*
	public function It_supports_mapping()
	{
		$domain = Prack_Builder::domain();
		$domain->
		  map( '/' )->wherein()
		    ->run( new Prack_Test_Echo( 200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'root' ) ) ) ) )->
		  map( '/sub' )->wherein()
		    ->run( new Prack_Test_Echo( 200, Prack::_Hash(), Prack::_Array( array( Prack::_String( 'sub'  ) ) ) ) );
		
		$mock_request = new Prack_Mock_Request( $domain->toMiddlewareApp() );
		$this->assertEquals( 'root', $mock_request->get( Prack::_String( '/'    ) )->getBody()->toN() );
		$this->assertEquals( 'sub',  $mock_request->get( Prack::_String( '/sub' ) )->getBody()->toN() );
	} // It supports mapping
	*/
	/*
	  it "supports mapping" do
    app = Rack::Builder.new do
      map '/' do |outer_env|
        run lambda { |inner_env| [200, {}, ['root']] }
      end
      map '/sub' do
        run lambda { |inner_env| [200, {}, ['sub']] }
      end
    end.to_app
    Rack::MockRequest.new(app).get("/").body.to_s.should.equal 'root'
    Rack::MockRequest.new(app).get("/sub").body.to_s.should.equal 'sub'
  end
  */

	/**
	 * It doesn't clone env even when mapping
	 * @author Joshua Morris
	 * @test
	 */
	/*
	public function It_doesn_t_clone_env_even_when_mapping()
	{
		$env_altering_middleware = new Prack_Test_Echo( 200, array(), array( 'root' ) );
		$env_altering_middleware->setEval( '$env[ \'new_key\' ] = \'new_value\';' );
		
		$domain = Prack_Builder::domain();
		$domain->
		  using( 'Prack_BuilderTest_NothingMiddleware' )->withArgs()->
		  map( '/' )->wherein()
		    ->run( $env_altering_middleware );
		
		$mock_request = new Prack_Mock_Request( $domain->toMiddlewareApp() );
		$this->assertEquals( 'root', $mock_request->get( '/' )->getBody() );
		$this->assertEquals( 'new_value', Prack_BuilderTest_NothingMiddleware::$env[ 'new_key' ] );
	} // It doesn't clone env even when mapping
	
	/**
	 * It chains apps by default
	 * @author Joshua Morris
	 * @test
	 */
	public function It_chains_apps_by_default()
	{
		
	} // It chains apps by default
}