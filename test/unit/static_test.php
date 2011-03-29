<?php

// CLI compatibility
chdir( dirname ( __FILE__ ) );

// TODO: Document!
class Prack_StaticTest_DummyApp
  implements Prack_I_MiddlewareApp
{
	// TODO: Document!
	public function call( &$env )
	{
		return array( 200, array(), 'Hello World' );
	}
}

// TODO: Document!
class Prack_StaticTest extends PHPUnit_Framework_TestCase 
{
	// TODO: Document!
	static function options()
	{
		return array( 'urls' => array( '/cgi' ), 'root' => dirname( __FILE__ ) );
	}
	
	// TODO: Document!
	function setUp()
	{
		$this->request = Prack_Mock_Request::with(
		  new Prack_Static( new Prack_StaticTest_DummyApp(), self::options() )
		);
	}
	
	/**
	 * It serves files
	 * @author Joshua Morris
	 * @test
	 */
	public function It_serves_files()
	{
		$response = $this->request->get( '/cgi/test' );
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/ruby/', $response->getBody() );
	} // It serves files
	
	/**
	 * It 404s if url root is known but it can't find the file
	 * @author Joshua Morris
	 * @test
	 */
	public function It_404s_if_url_root_is_known_but_it_can_t_find_the_file()
	{
		$response = $this->request->get( '/cgi/foo' );
		$this->assertTrue( $response->isNotFound() );
	} // It 404s if url root is known but it can't find the file
	
	/**
	 * It calls down the chain if url root is not known
	 * @author Joshua Morris
	 * @test
	 */
	public function It_calls_down_the_chain_if_url_root_is_not_known()
	{
		$response = $this->request->get( '/something/else' );
		$this->assertTrue( $response->isOK() );
		$this->assertRegExp( '/Hello World/', $response->getBody() );
	} // It calls down the chain if url root is not known
}