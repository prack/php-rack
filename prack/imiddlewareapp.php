<?php

interface Prack_IMiddlewareApp
{
	public function call(&$env);
}