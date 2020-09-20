<?php

namespace Tests\Unit\Support;

use Eliepse\Argile\Honeypot\Support\HoneypotManager;
use Exception;
use PHPUnit\Framework\TestCase;

class HoneypotManagerTest extends TestCase
{
	/** @test */
	public function it_throws_exception_when_honeypot_not_initialized()
	{
		$this->expectException(Exception::class);
		HoneypotManager::hash('foo');
	}
}
