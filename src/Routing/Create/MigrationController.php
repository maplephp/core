<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing\Migrations\Create;

use Psr\Http\Message\ResponseInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use MaplePHP\Core\Routing\DefaultCommand;

class MigrateController extends DefaultCommand
{
	private ?AbstractSchemaManager $schemaManager = null;
	
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