<?php

declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class CreateEventTypesTable extends AbstractMigration {
	public function up(): void {
		$this->execute(
			'CREATE TABLE event_types (
				event_type_id SERIAL NOT NULL
					PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				CONSTRAINT uq_event_types_name
					UNIQUE (name)
			);'
		);
	}

	public function down(): void {
		$this->execute('DROP TABLE event_types;');
	}
}
