<?php

// TODO: Document!
interface Prack_Interface_WritableStreamlike
{
	public function puts();
	public function write( $string );
	public function flush();
}