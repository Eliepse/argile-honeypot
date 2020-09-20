<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Unit;

use Eliepse\Argile\Honeypot\Honeypot;
use Exception;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
	/** @test */
	public function it_invalidates_cooldown()
	{
		$this->assertFalse((new Honeypot([], 10))->isCooldownPassed());
		$this->assertTrue((new Honeypot([], -1))->isCooldownPassed());
	}


	/** @test */
	public function it_throws_exception_when_not_in_session()
	{
		$this->expectException(Exception::class);
		Honeypot::load();
	}


	/** @test */
	public function it_creates_new_when_not_in_session()
	{
		$this->assertInstanceOf(Honeypot::class, Honeypot::loadOrNew());
	}


	/** @test */
	public function it_stores_in_session()
	{
		$pot = new Honeypot();
		Honeypot::store($pot);
		$this->assertEquals($pot, Honeypot::load());
	}


	/** @test */
	public function it_retreive_hash_and_names()
	{
		$pot = new Honeypot([$name = 'firstname']);
		$token = $pot->getToken();
		$hash = Honeypot::hash($name, $token);
		$this->assertEquals($hash, $pot->getHash($name));
		$this->assertEquals($name, $pot->getName($hash));
	}


	/** @test */
	public function it_stores_new_inputs()
	{
		$pot = new Honeypot(['firstname']);
		$hash = $pot->addInput($name = 'lastname');
		$this->assertEquals($name, $pot->getName($hash));
	}


	/** @test */
	public function it_detects_honeypots()
	{
		$pot = new Honeypot(['firstname']);
		$this->assertTrue($pot->isHoneypot('firstname'));
		$this->assertFalse($pot->isHoneypot('lastname'));
	}
}
