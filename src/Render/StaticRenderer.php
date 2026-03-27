<?php

namespace MaplePHP\Core\Render;

use MaplePHP\Core\App;
use MaplePHP\Http\Interfaces\PathInterface;
use Psr\Http\Message\ResponseInterface;

class StaticRenderer
{
	private PathInterface $path;
	private ResponseInterface $response;

	function __construct(ResponseInterface $response, PathInterface $path)
	{
		$this->response = $response;
		$this->path = $path;
	}

	function welcome(): string
	{
		$twigLink = $this->path->uri()->withPath("/hello/World");
		return $this->renderFile("welcome.php", ["twigLink" => $twigLink]);
	}

	private function renderFile(string $fileName, array $data = [])
	{
		$response = $this->response;
		$configs = App::get()->configs();
		$configs = isset($configs['configs']) ? $configs['configs'] : ['app_title' => "MaplePHP"];

		extract($data);

		ob_start();
		require App::get()->coreDir() . '/Render/Templates/' . $fileName;
		return ob_get_clean();
	}
}