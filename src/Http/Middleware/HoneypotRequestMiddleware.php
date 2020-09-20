<?php

namespace Eliepse\Argile\Honeypot\Http\Middleware;

use Eliepse\Argile\Honeypot\Honeypot;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class HoneypotRequestMiddleware implements MiddlewareInterface
{
	private Honeypot $honeypot;
	private int $cooldown;


	public function __construct(int $cooldown = 5) {
		$this->cooldown = $cooldown;
	}


	public function process(Request $request, RequestHandler $handler): ResponseInterface
	{
		if (strtoupper($request->getMethod()) !== "POST")
			return new Response(403);

		if (! Honeypot::isInSession()) {
			return new Response(403);
		}

		$this->honeypot = Honeypot::load();

		$inputs = $request->getParsedBody();
		$inputs = is_array($inputs) ? array_filter($inputs) : [];

		if (! $this->honeypot->isCooldownPassed($this->cooldown))
			return new Response(403);

		if ($this->areHoneypotsFilled($inputs))
			return new Response(403);

		$request = $request->withParsedBody($this->restoreNames($inputs));
		return $handler->handle($request);
	}


	private function areHoneypotsFilled(array $inputs): bool
	{
		foreach ($inputs as $name => $value) {
			if ($this->honeypot->isHoneypot($name)) {
				return true;
			}
		}

		return false;
	}


	private function restoreNames(array $inputs): array
	{
		$parsed_input = [];
		foreach ($inputs as $name => $value) {
			if ($this->honeypot->isHoneypot($name)) {
				continue;
			}
			$key = $this->honeypot->getName($name);
			$parsed_input[ $key ] = $value;
		}
		return $parsed_input;
	}
}