<?php

// CLI compatibility
chdir( dirname ( __FILE__ ) );

// TODO: Document!
class Prack_StaticTest_DummyApp
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	public function call( $env )
	{
		return Prb::Ary( array(
		  Prb::Num( 200 ),
		  Prb::Hsh(),
		  Prb::Str( 'Hello World' )
		) );
	}
}

// TODO: Document!
class Prack_StaticTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function options()
	{
		return Prb::Hsh( array(
		  'urls' => Prb::Ary( array( Prb::Str( '/cgi' ) ) ),
		  'root' => Prb::Str( dirname( __FILE__ ) )
		) );
	}
	
	// TODO: Document!
	function setUp()
	{
		$this->request = Prack_Mock_Request::with(
		  Prack_Static::with( new Prack_StaticTest_DummyApp(), self::options() )
		);
	}
	
	/**
	 * It serves files
	 * @author Joshua Morris
	 * @test
	 */
	public function It_serves_files()
	{
		$response = $this->request->get( Prb::Str( '/cgi/test' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertTrue( $response->getBody()->match( '/ruby/' ) );
	} // It serves files
	
	/**
	 * It 404s if url root is known but it can't find the file
	 * @author Joshua Morris
	 * @test
	 */
	public function It_404s_if_url_root_is_known_but_it_can_t_find_the_file()
	{
		$response = $this->request->get( Prb::Str( '/cgi/foo' ) );
		$this->assertTrue( $response->isNotFound() );
	} // It 404s if url root is known but it can't find the file
	
	/**
	 * It calls down the chain if url root is not known
	 * @author Joshua Morris
	 * @test
	 */
	public function It_calls_down_the_chain_if_url_root_is_not_known()
	{
		$response = $this->request->get( Prb::Str( '/something/else' ) );
		$this->assertTrue( $response->isOK() );
		$this->assertTrue( $response->getBody()->match( '/Hello World/' ) );
	} // It calls down the chain if url root is not known
}