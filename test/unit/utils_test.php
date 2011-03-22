<?php


class Prack_UtilsTest extends PHPUnit_Framework_TestCase 
{
	/**
	 * It should escape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly()
	{
		$this->assertEquals( 'fo%3Co%3Ebar', Prack_Utils::singleton()->escape( Prb::Str( 'fo<o>bar' ) )->raw() );
		$this->assertEquals( 'a+space', Prack_Utils::singleton()->escape( Prb::Str( 'a space' ) )->raw() );
		$this->assertEquals( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C',
		                     Prack_Utils::singleton()->escape( Prb::Str( "q1!2\"'w$5&7/z8)?\\" ) )->raw() );
		
	} // It should escape correctly
	
	/**
	 * It should escape correctly for multibyte characters
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_escape_correctly_for_multibyte_characters()
	{
		// FIXME: Implement unpack functionality on strings.
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4\xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( Prb::Str( "%E3%81%BE%E3%81%A4%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prb::Str( $unpacked[ 1 ] ) ) );
		$unpacked = unpack( 'a*', "\xE3\x81\xBE\xE3\x81\xA4 \xE3\x82\x82\xE3\x81\xA8" ); // Matsumoto
		$this->assertEquals( Prb::Str( "%E3%81%BE%E3%81%A4+%E3%82%82%E3%81%A8" ),
		                     Prack_Utils::singleton()->escape( Prb::Str( $unpacked[ 1 ] ) ) );
	} // It should escape correctly for multibyte characters
	
	/**
	 * It should unescape correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_unescape_correctly()
	{
		$this->assertEquals( 'fo<o>bar', Prack_Utils::singleton()->unescape( Prb::Str( 'fo%3Co%3Ebar' ) )->raw() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prb::Str( 'a+space' ) )->raw() );
		$this->assertEquals( 'a space', Prack_Utils::singleton()->unescape( Prb::Str( 'a%20space' ) )->raw() );
		$this->assertEquals( "q1!2\"'w$5&7/z8)?\\",
		                     Prack_Utils::singleton()->unescape( Prb::Str( 'q1%212%22%27w%245%267%2Fz8%29%3F%5C' ) )->raw() );
	} // It should unescape correctly
	
	/**
	 * It should parse query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_query_strings_correctly()
	{
		$this->assertEquals( array( 'foo' => Prb::Str( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'foo=bar' ) )->raw() );
		$this->assertEquals( array( 'foo' => Prb::Str( '"bar"' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'foo="bar"' ) )->raw() );
		$this->assertEquals( array( 'foo' => Prb::Ary( Prb::Str( 'bar' ), Prb::Str( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'foo=bar&foo=quux' ) )->raw() );
		$this->assertEquals( array( 'foo' => Prb::Str( '1' ), 'bar' => Prb::Str( '2' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'foo=1&bar=2' ) )->raw() );
		$this->assertEquals( array( 'my weird field' => Prb::Str( "q1!2\"'w$5&7/z8)?" ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F' ) )->raw() );
		$this->assertEquals( array( 'foo=baz' => Prb::Str( 'bar' ) ),
		                     Prack_Utils::singleton()->parseQuery( Prb::Str( 'foo%3Dbaz=bar' ) )->raw() );
	} // It should parse query strings correctly
	
	/**
	 * It should parse nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_parse_nested_query_strings_correctly()
	{
		$this->assertEquals( Prb::Hsh( array( 'foo' => null ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=bar' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( '"bar"' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo="bar"' ) ) );
		
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( 'quux' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=bar&foo=quux' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo&foo=' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( '1' ), 'bar' => Prb::Str( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=1&bar=2' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( '1' ), 'bar' => Prb::Str( '2' ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( '&foo=1&bar=2' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => null, 'bar' => Prb::Str() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo&bar=' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ), 'baz' => Prb::Str() ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=bar&baz=' ) ) );
		$this->assertEquals( Prb::Hsh( array( 'my weird field' => Prb::Str( "q1!2\"'w$5&7/z8)?" ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( "my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F" ) ) );
		
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str() ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( "foo[]=" ) ) );
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str( 'bar' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( "foo[]=bar" ) ) );
		
		$this->assertEquals( Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str( '1' ), Prb::Str( '2' ) ) ) ) ),
		                     Prack_Utils::singleton()->parseNestedQuery( Prb::Str( "foo[]=1&foo[]=2" ) ) );
		$this->assertEquals(
		  Prb::Hsh( array(
		    'foo' => Prb::Str( 'bar' ),
		    'baz' => Prb::Ary( array(
		      Prb::Str( '1' ),
		      Prb::Str( '2' ),
		      Prb::Str( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'foo' => Prb::Ary( array( Prb::Str( 'bar' ) ) ),
		    'baz' => Prb::Ary( array(
		      Prb::Str( '1' ),
		      Prb::Str( '2' ),
		      Prb::Str( '3' )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'foo[]=bar&baz[]=1&baz[]=2&baz[]=3' ) )
		);
		
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Str( '1' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][z]=1' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Ary( array(
		          Prb::Str( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][z][]=1' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Str( '2' )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][z]=1&x[y][z]=2' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Ary( array(
		          Prb::Str( '1' ), Prb::Str( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][z][]=1&x[y][z][]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 'z' => Prb::Str( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z]=1' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Ary( array( 
		            Prb::Str( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z][]=1' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'w' => Prb::Str( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z]=1&x[y][][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'v' => Prb::Hsh( array(
		            'w' => Prb::Str( '1 ')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][v][w]=1' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'v' => Prb::Hsh( array( 
		            'w' => Prb::Str( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z]=1&x[y][][v][w]=2' ) )
		);
		
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		        ) ),
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z]=1&x[y][][z]=2' ) )
		);
		$this->assertEquals(
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'w' => Prb::Str( 'a' )
		        ) ),
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '2' ),
		          'w' => Prb::Str( '3' )
		        ) ),
		      ) )
		    ) )
		  ) ),
		  Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y][][z]=1&x[y][][w]=a&x[y][][z]=2&x[y][][w]=3' ) )
		);
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y]=1&x[y]z=2' ) );
		} catch ( Prb_Exception_Type $e1 ) { }
		
		if ( isset( $e1 ) )
			$this->assertEquals( "expected Prb_Hash (got Prb_String) for param 'y'",
			                     $e1->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected hash, got string." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y]=1&x[]=1' ) );
		} catch ( Prb_Exception_Type $e2 ) { }
		
		if ( isset( $e2 ) )
			$this->assertEquals( "expected Prb_Array (got Prb_Hash) for param 'x'",
			                     $e2->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected array, got hash." );
		
		try
		{
			Prack_Utils::singleton()->parseNestedQuery( Prb::Str( 'x[y]=1&x[y][][w]=2' ) );
		} catch ( Prb_Exception_Type $e3 ) { }
		
		if ( isset( $e3 ) )
			$this->assertEquals( "expected Prb_Array (got Prb_String) for param 'y'",
			                     $e3->getMessage() );
		else
			$this->fail( "Expecting exception on malformed query string: expected array, got string." );
	} // It should parse nested query strings correctly
	
	/**
	 * It should build query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_query_strings_correctly()
	{
		$this->assertEquals(
		  'foo=bar',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
		  )->raw()
		);
		$this->assertEquals(
		  'foo=bar&foo=quux',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::Hsh( array(
		      'foo' => Prb::Ary( array(
		        Prb::Str( 'bar'  ),
		        Prb::Str( 'quux' )
		      ) )
		    ) )
		  )->raw()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildQuery(
		    Prb::Hsh( array(
		      'my weird field' => Prb::Str( "q1!2\"'w$5&7/z8)?" )
		    ) )
		  )->raw()
		);
	} // It should build query strings correctly
	
	/**
	 * It should build nested query strings correctly
	 * @author Joshua Morris
	 * @test
	 */
	public function It_should_build_nested_query_strings_correctly()
	{
		$this->assertEquals(
		  'foo',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array( 'foo' => null ) )
		  )->raw()
		);
		$this->assertEquals(
		  'foo=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array( 'foo' => Prb::Str() ) )
		  )->raw()
		);
		$this->assertEquals(
		  'foo=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ) ) )
		  )->raw()
		);
		
		$this->assertEquals(
		  'foo=1&bar=2',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array(
		      'foo' => Prb::Str( '1' ),
		      'bar' => Prb::Str( '2' )
		    ) )
		  )->raw()
		);
		$this->assertEquals(
		  'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array(
		      'my weird field' => Prb::Str( "q1!2\"'w$5&7/z8)?" ),
		    ) )
		  )->raw()
		);
		
		$this->assertEquals(
		  'foo[]',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array(
		      'foo' => Prb::Ary( array( null ) ),
		    ) )
		  )->raw()
		);
		$this->assertEquals(
		  'foo[]=',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array(
		      'foo' => Prb::Ary( array( Prb::Str() ) ),
		    ) )
		  )->raw()
		);
		$this->assertEquals(
		  'foo[]=bar',
		  Prack_Utils::singleton()->buildNestedQuery(
		    Prb::Hsh( array(
		      'foo' => Prb::Ary( array( Prb::Str( 'bar' ) ) ),
		    ) )
		  )->raw()
		);
		
		# Test that build_nested_query performs the inverse
		# function of parse_nested_query.
		foreach( array(
		  Prb::Hsh( array( 'foo' => null, 'bar' => Prb::Str() ) ),
		  Prb::Hsh( array( 'foo' => Prb::Str( 'bar' ), 'baz' => Prb::Str() ) ),
		  Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str() ) ) ) ),
		  Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str( 'bar' ) ) ) ) ),
		  Prb::Hsh( array(
		    'foo' => Prb::Str( 'bar' ),
		    'baz' => Prb::Ary( array(
		      Prb::Str( '1' ),
		      Prb::Str( '2' ),
		      Prb::Str( '3' )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'foo' => Prb::Ary( array( Prb::Str( 'bar' ) ) ),
		    'baz' => Prb::Ary( array(
		      Prb::Str( '1' ),
		      Prb::Str( '2' ),
		      Prb::Str( '3' )
		    ) )
		  ) ),
		  Prb::Hsh( array( 'foo' => Prb::Ary( array( Prb::Str( '1' ), Prb::Str( '2' ) ) ) ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Str( '1' )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Ary( array(
		          Prb::Str( '1' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Hsh( array(
		        'z' => Prb::Ary( array(
		          Prb::Str( '1' ), Prb::Str( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 'z' => Prb::Str( '1' ) ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Ary( array( 
		            Prb::Str( '1' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'w' => Prb::Str( '2' )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'v' => Prb::Hsh( array(
		            'w' => Prb::Str( '1')
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'v' => Prb::Hsh( array( 
		            'w' => Prb::Str( '2' )
		          ) )
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		        ) ),
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '2' ),
		        ) )
		      ) )
		    ) )
		  ) ),
		  Prb::Hsh( array(
		    'x' => Prb::Hsh( array(
		      'y' => Prb::Ary( array(
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '1' ),
		          'w' => Prb::Str( 'a' )
		        ) ),
		        Prb::Hsh( array( 
		          'z' => Prb::Str( '2' ),
		          'w' => Prb::Str( '3' )
		        ) ),
		      ) )
		    ) )
		  ) )
		) as $params ) {
			$query_string = Prack_Utils::singleton()->buildNestedQuery( $params );
			$this->assertEquals( $params, Prack_Utils::singleton()->parseNestedQuery( $query_string ) );
		}
		
		try
		{
			Prack_Utils::singleton()->buildNestedQuery( Prb::Str( 'foo=bar' ) );
		} catch ( Exception $e4 ) {}
		
		if ( isset( $e4 ) )
			$this->assertEquals( 'value must be a Prb_Hash', $e4->getMessage() );
		else
			$this->fail( "Expected exception on nested query building when query isn't nested." );
	} // It should build nested query strings correctly
}