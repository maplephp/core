<?php

namespace MaplePHP\Core\Support\Database;

use MaplePHP\Core\Support\Database\DB;
use Doctrine\DBAL\Schema\Schema;

abstract class Migrations
{
	protected DB $db;
	
	public function __construct()
	{
		$this->db = DB::instance();
	}

	/**
	 * Migrate up
	 *
	 * @param Schema $schema
	 * @return void
	 */
	abstract public function up(Schema $schema): void;

	/**
	 * Migrate down
	 * 
	 * @param Schema $schema
	 * @return void
	 */
	abstract public function down(Schema $schema): void;
}