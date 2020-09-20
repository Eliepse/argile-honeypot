<?php

namespace Eliepse\Argile\Honeypot\Support;

use Eliepse\Argile\Honeypot\Honeypot;

class HoneypotManager
{
	protected static function getInstance(): Honeypot
	{
		return Honeypot::load();
	}


	public static function hash(string $name): string
	{
		return self::getInstance()->getHash($name);
	}


	public static function name(string $hash): string
	{
		return self::getInstance()->getName($hash);
	}
}