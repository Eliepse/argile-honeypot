<?php

namespace Eliepse\Argile\Honeypot\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class HoneypotRequestMiddleware implements MiddlewareInterface
{
	public static int $minDelaySec = 5;

	private string $key = '';
	private array $names = [];
	private int $generated_at = 0;


	public function process(Request $request, RequestHandler $handler): ResponseInterface
	{
		if (strtoupper($request->getMethod()) !== "POST")
			return new Response(403);

		// If the session does not have data, we cancel the resquest
		if (array_diff(['honeypot_key', 'honeypot_names', 'honeypot_generated_at'], array_keys($_SESSION))) {
			return new Response(403);
		}


		$this->key = $_SESSION['honeypot_key'];
		$this->names = $_SESSION['honeypot_names'];
		$this->generated_at = $_SESSION['honeypot_generated_at'];

		$inputs = $request->getParsedBody();
		$inputs = is_array($inputs) ? array_filter($inputs) : [];

		if ($this->isRequestTooEarly())
			return new Response(403);

		if ($this->areHoneypotsFilled($inputs))
			return new Response(403);

		$request = $request->withParsedBody($this->restoreNames($inputs));

		return $handler->handle($request);
	}


	private function areHoneypotsFilled(array $inputs): bool
	{
		return count(array_intersect($this->names, array_keys($inputs))) > 0;
	}


	private function isRequestTooEarly(): bool
	{
		return time() - $this->generated_at < self::$minDelaySec;
	}


	private function restoreNames(array $inputs): array
	{
		// The manifest map hashed names with their original value
		$manifest = array_combine(
			array_map(fn($val) => md5($val . $this->key), $this->names),
			$this->names
		);
		$parsed_input = [];
		foreach ($inputs as $name => $value) {
			/** @phpstan-ignore-next-line */
			$key = $manifest[ $name ] ?? $name;
			$parsed_input[ $key ] = $value;
		}
		return $parsed_input;
	}
}