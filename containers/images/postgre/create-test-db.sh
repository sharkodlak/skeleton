#!/bin/bash
# Creates the test database as an empty sibling of the application database.
#
# The test suite runs its own Phinx migrations against this database, so it can
# never overwrite the data in $POSTGRES_DB. The script runs once as an initdb
# step and is idempotent, so `make db-create-test` can also create the database
# on a cluster that was initialised before this script existed.
set -euo pipefail

: "${POSTGRES_USER:?POSTGRES_USER must be set}"
: "${POSTGRES_DB:?POSTGRES_DB must be set}"

TEST_DB_NAME="${POSTGRES_DB}_test"

exists=$(psql --username "$POSTGRES_USER" --dbname postgres --tuples-only --no-align \
	--command "SELECT 1 FROM pg_database WHERE datname = '${TEST_DB_NAME}'")

if [ "$exists" = "1" ]; then
	echo "Database ${TEST_DB_NAME} already exists, nothing to do."
	exit 0
fi

# No explicit locale: inheriting template1 keeps the test database byte for byte
# comparable to the application database, whatever POSTGRES_INITDB_ARGS set up.
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname postgres \
	--command "CREATE DATABASE ${TEST_DB_NAME} OWNER ${POSTGRES_USER};"

echo "Database ${TEST_DB_NAME} created."
