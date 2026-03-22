<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Migrations\Create;

use RuntimeException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCreateController extends DefaultShellController
{

	protected const TYPE = false;
	
	/**
	 * Run ALL migrations (up), or a single one if --name is provided.
	 */
	public function index(ResponseInterface $response): ResponseInterface
	{
		if (!$this->confirm("Are you sure you want to run ALL migrations?")) {
			return $response;
		}

		$this->runDirection('up');

		return $response;
	}
}