<?php

namespace Eliepse\Argile\Honeypot\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class HoneypotResponseMiddleware implements MiddlewareInterface
{
	private string $key = '';
	private array $names = [];


	public function process(Request $request, RequestHandler $handler): ResponseInterface
	{
		$content = (string)$handler->handle($request)->getBody();

		$this->key = bin2hex(random_bytes(5));

		$content = $this->handleHoneypots($content);

		$_SESSION['honeypot_key'] = $this->key;
		$_SESSION['honeypot_names'] = $this->names;
		$_SESSION['honeypot_generated_at'] = time();

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
		$this->names[] = $name;
		preg_match('/type="([a-zA-Z]+)"/', $html_input, $matches);
		$type = $matches[1] ?? 'text';
		$honeypot = "<input class=\"onipat\" type=\"$type\" name=\"$name\" autocomplete=\"off\" tabindex=\"-1\">";
		return "$honeypot\n" . preg_replace("/honeypot:$name/", $this->hashName($name), $html_input);
	}


	private function hashName(string $name): string
	{
		return md5($name . $this->key);
	}
}