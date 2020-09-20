<?php

namespace Eliepse\Argile\Honeypot;

use ErrorException;

final class Honeypot
{
	private string $token;
	/** @var array<string, string> */
	private array $manifest;
	private int $cooldown;
	private int $generated_at;


	public function __construct(array $names = [], int $cooldown = 5)
	{
		$this->token = self::generateToken();
		/** @phpstan-ignore-next-line */
		$this->manifest = array_combine(array_map(fn($name) => $this->getHash($name), $names), $names);
		$this->cooldown = $cooldown;
		$this->generated_at = time();
	}


	public function addInput(string $name): string
	{
		$hash = self::hash($name, $this->token);
		$this->manifest[ $hash ] = $name;
		return $hash;
	}


	public function getName(string $hash): string
	{
		return $this->manifest[ $hash ] ?? $hash;
	}


	public function getHash(string $name): string
	{
		return self::hash($name, $this->token);
	}


	public function getToken(): string
	{
		return $this->token;
	}


	public function isCooldownPassed(): bool
	{
		return $this->generated_at + $this->cooldown < time();
	}


	public function isHoneypot(string $name): bool
	{
		return in_array($name, $this->manifest, true);
	}


	public static function generateToken(): string
	{
		return bin2hex(random_bytes(5));
	}


	public static function hash(string $name, string $token): string
	{
		return md5($name . $token);
	}


	public static function isInSession(): bool
	{
		return isset($_SESSION["honeypot"]) && is_a($_SESSION["honeypot"], self::class);
	}


	public static function load(): Honeypot
	{
		if (Honeypot::isInSession()) {
			return $_SESSION["honeypot"];
		}

		throw new ErrorException(
			"Honeypot has not been initialized. Make sure you put middleware in the right order, " .
			"or create a new instance first and save it."
		);
	}


	public static function loadOrNew(array $names = [], int $cooldown = 5): self
	{
		if (self::isInSession()) {
			return $_SESSION["honeypot"];
		}

		return new Honeypot($names, $cooldown);
	}


	public static function store(self $honeypot): void
	{
		$_SESSION["honeypot"] = $honeypot;
	}
}