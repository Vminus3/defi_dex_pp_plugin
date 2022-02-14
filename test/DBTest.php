<?php

namespace php;

use DB;
use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
	protected DB $DB;

	protected function setUp()
	{
		$this->DB = new DB();
	}

	public function testGetToken()
	{

	}
}
