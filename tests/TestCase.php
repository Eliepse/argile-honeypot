<?php

namespace Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		$_SESSION = [];
	}
}