<?php

// TODO: Document!
class Prack_Auth_Digest_Nonce
{
	static $private_key = null;
	static $time_limit  = null;
	
	private $timestamp;
	private $given_digest;
	
	// TODO: Document!
	static function privateKey()
	{
		if ( is_null( self::$private_key ) )
			self::$private_key = Prb::Str();
		
		return self::$private_key;
	}
	
	// TODO: Document!
	static function setPrivateKey( $pk )
	{
		self::$private_key = $pk;
	}
	
	// TODO: Document!
	static function timeLimit()
	{
		if ( is_null( self::$time_limit ) )
			self::$time_limit = null;
		
		return self::$time_limit;
	}
	
	// TODO: Document!
	static function setTimeLimit( $tl )
	{
		self::$time_limit = $tl;
	}
	
	// TODO: Document!
	static function parse( $string )
	{
		$split = $string->base64Decode()->split( '/ /', 2 );
		return Prack_Auth_Digest_Nonce::with(
		  Prb::Time( $split->get( 0 )->toN()->raw() ),
		  $split->get( 1 )
		);
	}
	
	// TODO: Document!
	static function with( $time = null, $given_digest = null )
	{
		return new Prack_Auth_Digest_Nonce( $time, $given_digest );
	}
	
	// TODO: Document!
	function __construct( $time = null, $given_digest = null )
	{
		if ( is_null( $time ) )
			$time = Prb::Time();
		
		$this->timestamp    = $time->getSeconds();
		$this->given_digest = $given_digest;
	}
	
	// TODO: Document!
	public function toS()
	{
		return Prb::Ary( array(
		  $this->timestamp->toS(),
		  $this->digest()
		) )->join( Prb::Str( ' ' ) )
		   ->base64Encode();
	}
	
	// TODO: Document!
	public function digest()
	{
		return Prb::Str( md5(
		  Prb::Ary( array(
		    $this->timestamp->toS(),
		    self::privateKey()
		  ) )->join( Prb::Str( ':' ) )->raw()
		) );
	}
	
	// TODO: Document!
	public function isValid()
	{
		return ( $this->digest() == $this->given_digest );
	}
	
	// TODO: Document!
	public function isStale()
	{
		$tl = self::timeLimit();
		return isset( $tl ) && ( $this->timestamp->raw() - Prb::Time()->getSeconds()->raw() < $tl->raw() );
	}
	
	// TODO: Document!
	public function isFresh()
	{
		return !( $this->isStale() );
	}
}
