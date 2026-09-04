<?php

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class EnableUuidExtension extends AbstractMigration {
	public function change(): void {
		$this->execute('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
	}
}
