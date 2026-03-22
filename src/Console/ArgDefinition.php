<?php

declare(strict_types=1);

namespace MaplePHP\Core\Console;

class ArgDefinition
{
	public function __construct(
		public readonly string $name,
		public readonly string $description,
		public readonly bool   $required = false
	)
	{
	}
}