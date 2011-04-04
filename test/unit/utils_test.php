<?php


class Prack_UtilsTest extends PHPUnit_Framework_TestCase 
{
	private $utils;
	
	// TODO: Document!
	public function setUp()
	{
		$this->utils = Prack_Utils::singleton();
	}
	
	/**
	 * It should escape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly()
	{
		$this->assertEquals( 'fo%3Co%3Ebar', $this->utils->escape( 'fo<o>bar' ) );
		$this->assertEquals( 'a+space',      $this->utils->escape( 'a space'  ) );
		$this->assertEquals( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C', $this->utils->escape( "q1!2\"'w$5&7/z8)?\\" ) );
	} // It should escape correctly
	
	/**
	 * It should escape correctly for multibyte characters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly_for_multibyte_characters()
	{
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4\xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( '%E3%81%BE%E3%81%A4%E3%82%82%E3%81%A8', $this->utils->escape( $unpacked[ 1 ] ) );
		
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4 \xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( '%E3%81%BE%E3%81%A4+%E3%82%82%E3%81%A8', $this->utils->escape( $unpacked[ 1 ] ) );
	} // It should escape correctly for multibyte characters
	
	/**
	 * It should unescape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_unescape_correctly()
	{
		$this->assertEquals( 'fo<o>bar',            $this->utils->unescape( 'fo%3Co%3Ebar' ) );
		$this->assertEquals( 'a space',             $this->utils->unescape( 'a+space'      ) );
		$this->assertEquals( 'a space',             $this->utils->unescape( 'a%20space'    ) );
		$this->assertEquals( "q1!2\"'w$5&7/z8)?\\", $this->utils->unescape( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C' ) );
	} // It should unescape correctly
	
	/**
	 * It should parse query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_query_strings_correctly()
	{
		$this->assertEquals( array( 'foo' => 'bar'                          ), $this->utils->parseQuery( 'foo=bar'                                         ) );
		$this->assertEquals( array( 'foo' => '"bar"'                        ), $this->utils->parseQuery( 'foo="bar"'                                       ) );
		$this->assertEquals( array( 'foo' => array( 'bar', 'quux' )         ), $this->utils->parseQuery( 'foo=bar&foo=quux'                                ) );
		$this->assertEquals( array( 'foo' => '1', 'bar' => '2'              ), $this->utils->parseQuery( 'foo=1&bar=2'                                     ) );
		$this->assertEquals( array( 'my weird field' => "q1!2\"'w$5&7/z8)?" ), $this->utils->parseQuery( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F' ) );
		$this->assertEquals( array( 'foo=baz' => 'bar'                      ), $this->utils->parseQuery( 'foo%3Dbaz=bar'                                   ) );
	} // It should parse query strings correctly
	
	/**
	 * It should parse nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_nested_query_strings_correctly()
	{
		$this->assertEquals( array( 'foo' => null    ), $this->utils->parseNestedQuery( 'foo'              ) );
		$this->assertEquals( array( 'foo' => ''      ), $this->utils->parseNestedQuery( 'foo='             ) );
		$this->assertEquals( array( 'foo' => 'bar'   ), $this->utils->parseNestedQuery( 'foo=bar'          ) );
		$this->assertEquals( array( 'foo' => '"bar"' ), $this->utils->parseNestedQuery( 'foo="bar"'        ) );
		$this->assertEquals( array( 'foo' => 'quux'  ), $this->utils->parseNestedQuery( 'foo=bar&foo=quux' ) );
		$this->assertEquals( array( 'foo' => ''      ), $this->utils->parseNestedQuery( 'foo&foo='         ) );
		
		$this->assertEquals( array( 'foo' =>   '1', 'bar' => '2' ), $this->utils->parseNestedQuery( 'foo=1&bar=2'  ) );
		$this->assertEquals( array( 'foo' =>   '1', 'bar' => '2' ), $this->utils->parseNestedQuery( '&foo=1&bar=2' ) );
		$this->assertEquals( array( 'foo' =>  null, 'bar' => ''  ), $this->utils->parseNestedQuery( 'foo&bar='     ) );
		$this->assertEquals( array( 'foo' => 'bar', 'baz' => ''  ), $this->utils->parseNestedQuery( 'foo=bar&baz=' ) );
		$this->assertEquals( array( 'my weird field' => "q1!2\"'w$5&7/z8)?" ),
		                     $this->utils->parseNestedQuery( "my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F" ) );
		
		$this->assertEquals( array( 'foo' => array( ''    )     ), $this->utils->parseNestedQuery( "foo[]="          ) );
		$this->assertEquals( array( 'foo' => array( 'bar' )     ), $this->utils->parseNestedQuery( "foo[]=bar"       ) );
		$this->assertEquals( array( 'foo' => array( '1' , '2' ) ), $this->utils->parseNestedQuery( "foo[]=1&foo[]=2" ) );
		
		$this->assertEquals( array( 'foo' => 'bar', 'baz' => array( '1', '2', '3' ) ),
		                     $this->utils->parseNestedQuery( 'foo=bar&baz[]=1&baz[]=2&baz[]=3' ) );
		$this->assertEquals( array( 'foo' => array( 'bar' ), 'baz' => array( '1', '2', '3' ) ),
		                     $this->utils->parseNestedQuery( 'foo[]=bar&baz[]=1&baz[]=2&baz[]=3' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( 'z' => '1' ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][z]=1' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( 'z' => array( '1' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][z][]=1' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( 'z' => '2' ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][z]=1&x[y][z]=2' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( 'z' => array( '1', '2' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][z][]=1&x[y][z][]=2' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => '1' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z]=1' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => array( '1' ) ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z][]=1' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => '1' , 'w' => '2' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z]=1&x[y][][w]=2' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'v' => array( 'w' => '1' ) ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][v][w]=1' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => '1', 'v' => array( 'w' => '2' ) ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z]=1&x[y][][v][w]=2' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => '1' ), array( 'z' => '2' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z]=1&x[y][][z]=2' ) );
		$this->assertEquals( array( 'x' => array( 'y' => array( array( 'z' => '1', 'w' => 'a' ), array( 'z' => '2', 'w' => '3' ) ) ) ),
		                     $this->utils->parseNestedQuery( 'x[y][][z]=1&x[y][][w]=a&x[y][][z]=2&x[y][][w]=3' ) );
		
		try
		{
			$this->utils->parseNestedQuery( 'x[y]=1&x[y]z=2' );
		} catch ( Prb_Exception_Type $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertRegExp( "/expected type/", $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string." );
		
		try
		{
			$this->utils->parseNestedQuery( 'x[y]=1&x[]=1' );
		} catch ( Prb_Exception_Type $e2 ) { }
		
		if ( isset( $e2 ) )
			$this->assertRegExp( "/expected type/", $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string." );
		
		try
		{
			$this->utils->parseNestedQuery( 'x[y]=1&x[y][][w]=2' );
		} catch ( Prb_Exception_Type $e3 ) { }
		
		if ( isset( $e3 ) )
			$this->assertRegExp( "/expected type/", $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string." );
	} // It should parse nested query strings correctly
	
	/**
	 * It should build query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_query_strings_correctly()
	{
		$this->assertEquals( 'foo=bar',          $this->utils->buildQuery( array( 'foo' => 'bar' )                  ) );
		$this->assertEquals( 'foo=bar&foo=quux', $this->utils->buildQuery( array( 'foo' => array( 'bar', 'quux' ) ) ) );
		$this->assertEquals( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		                     $this->utils->buildQuery( array( 'my weird field' => "q1!2\"'w$5&7/z8)?" ) ) );
	} // It should build query strings correctly
	
	/**
	 * It should build nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_nested_query_strings_correctly()
	{
		$this->assertEquals( 'foo',         $this->utils->buildNestedQuery( array( 'foo' => null  )             ) );
		$this->assertEquals( 'foo=',        $this->utils->buildNestedQuery( array( 'foo' => ''    )             ) );
		$this->assertEquals( 'foo=bar',     $this->utils->buildNestedQuery( array( 'foo' => 'bar' )             ) );
		$this->assertEquals( 'foo=1&bar=2', $this->utils->buildNestedQuery( array( 'foo' => '1', 'bar' => '2' ) ) );
		$this->assertEquals( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		                     $this->utils->buildNestedQuery( array( 'my weird field' => "q1!2\"'w$5&7/z8)?"    ) ) );
		$this->assertEquals( 'foo[]',       $this->utils->buildNestedQuery( array( 'foo' => array( null  )     ) ) );
		$this->assertEquals( 'foo[]=',      $this->utils->buildNestedQuery( array( 'foo' => array( ''    )     ) ) );
		$this->assertEquals( 'foo[]=bar',   $this->utils->buildNestedQuery( array( 'foo' => array( 'bar' )     ) ) );
		
		foreach ( array(
			array( 'foo' =>  null, 'bar' => ''  ),
			array( 'foo' => 'bar', 'baz' => ''  ),
			array( 'foo' => array( '1' , '2' ) ),
			array( 'foo' => 'bar', 'baz' => array( '1', '2', '3' ) ),
			array( 'foo' => array( 'bar' ), 'baz' => array( '1', '2', '3' ) ),
			array( 'x' => array( 'y' => array( 'z' => '1' ) ) ),
			array( 'x' => array( 'y' => array( 'z' => array( '1' ) ) ) ),
			array( 'x' => array( 'y' => array( 'z' => array( '1', '2' ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => '1' ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => array( '1' ) ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => '1' , 'w' => '2' ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'v' => array( 'w' => '1' ) ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => '1', 'v' => array( 'w' => '2' ) ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => '1' ), array( 'z' => '2' ) ) ) ),
			array( 'x' => array( 'y' => array( array( 'z' => '1', 'w' => 'a' ), array( 'z' => '2', 'w' => '3' ) ) ) )
		) as $params ) {
			$query_string = $this->utils->buildNestedQuery( $params );
			$this->assertEquals( $params, $this->utils->parseNestedQuery( $query_string ) );
		}
		
		try
		{
			$this->utils->buildNestedQuery( 'foo=bar' );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertRegExp( '/param must/', $e4->getMessage() );
		else
			$this->fail( "Expected exception on nested query building." );
	} // It should build nested query strings correctly
	
	/**
	 * It should figure out which encodings are acceptable
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_figure_out_which_encodings_are_acceptable()
	{
		$this->assertEquals( null, $this->aeHelper( array(),           array(array('x', 1))          ));
		$this->assertEquals( null, $this->aeHelper( array('identity'), array(array('identity', 0.0)) ));
		$this->assertEquals( null, $this->aeHelper( array('identity'), array(array('*', 0.0))        ));
		
		$this->assertEquals( 'identity', $this->aeHelper( array('identity'), array(array('compress', 1.0),array('gzip', 1.0)) ));
		
		$this->assertEquals( 'compress', $this->aeHelper( array('compress','gzip','identity'), array(array('compress',1.0),array('gzip',1.0)) ));
		$this->assertEquals( 'gzip',     $this->aeHelper( array('compress','gzip','identity'), array(array('compress',0.5),array('gzip',1.0)) ));
		
		$this->assertEquals( 'identity', $this->aeHelper( array('foo','bar','identity'), array()                                   ));
		$this->assertEquals( 'foo',      $this->aeHelper( array('foo','bar','identity'), array(array('*',1.0))                     ));
		$this->assertEquals( 'bar',      $this->aeHelper( array('foo','bar','identity'), array(array('*',1.0),array('foo',0.9))    ));
		                                                                                       
		$this->assertEquals( 'identity', $this->aeHelper( array('foo','bar','identity'), array(array('foo',0),array('bar',0))      ));
		$this->assertEquals( 'identity', $this->aeHelper( array('foo','bar','identity'), array(array('*',0),array('identity',0.1)) ));
	} // It should figure out which encodings are acceptable
	
	/**
	 * @callback
	 */
	public function aeHelper( $a, $b )
	{
		return Prack_Utils::singleton()->selectBestEncoding( $a, $b );
	}
}