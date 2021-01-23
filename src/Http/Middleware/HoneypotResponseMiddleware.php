<?php

namespace Eliepse\Argile\Honeypot\Http\Middleware;

use Eliepse\Argile\Honeypot\Honeypot;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class HoneypotResponseMiddleware implements MiddlewareInterface
{
	private Honeypot $honeypot;


	public function process(Request $request, RequestHandler $handler): ResponseInterface
	{
		$this->honeypot = Honeypot::loadOrNew();

		// If the honeypot has never been initialized before, we create a new instance
		// that can be used to make the first view compilation.
		if (! Honeypot::isInSession()) {
			Honeypot::store($this->honeypot);
		}

		$content = (string)$handler->handle($request)->getBody();

		$content = $this->handleHoneypots($content);
		Honeypot::store($this->honeypot);

		$response = new Response();
		$response->getBody()->write($content);
		return $response;
	}


	private function handleHoneypots(string $html): string
	{
		$pattern = '/<input.*name="honeypot:([a-zA-Z0-9]+)".*>/sU';
		return preg_replace_callback($pattern, fn($mat) => $this->processHtmlInput($mat[0], $mat[1]), $html,) ?? $html;
	}


	private function processHtmlInput(string $html_input, string $name): string
	{
		$hash = $this->honeypot->addInput($name);
		preg_match('/type="([a-zA-Z]+)"/', $html_input, $matches);
		$type = $matches[1] ?? 'text';
		$honeypot = "<input class=\"onipat\" type=\"$type\" name=\"$name\" autocomplete=\"off\" tabindex=\"-1\">";
		return "$honeypot\n" . preg_replace("/honeypot:$name/", $hash, $html_input);
	}
}