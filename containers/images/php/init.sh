#!/bin/bash
# Startup sequence for the php container, shared by the development and the
# production image.
#
# Database migrations are deliberately not run here: schema changes are an
# explicit step (`make db-setup`), not a side effect of starting a container.
set -euo pipefail

readonly APP_DIR="${APP_DIR:-/app}"
readonly READY_MARKER='/tmp/app-ready'
readonly DB_WAIT_ATTEMPTS="${DB_WAIT_ATTEMPTS:-60}"

log_step() {
	echo "==> $1"
}

log_error() {
	echo "ERROR: startup step failed: $1" >&2
}

run_step() {
	local step_name="$1"
	shift

	log_step "$step_name"

	if ! "$@"; then
		log_error "$step_name"
		exit 1
	fi
}

database_is_reachable() {
	php -r '
		$dsn = sprintf(
			"pgsql:host=%s;port=%d;dbname=%s",
			getenv("DB_HOST"),
			(int) getenv("DB_PORT"),
			getenv("DB_NAME")
		);

		try {
			new PDO($dsn, getenv("DB_USER"), getenv("DB_PASS"));
		} catch (Throwable $exception) {
			exit(1);
		}
	' 2>/dev/null
}

# Compose already orders startup through `depends_on: condition: service_healthy`,
# but the image also runs outside compose, where nothing does.
wait_for_database() {
	local attempt=1

	while ! database_is_reachable; do
		if [ "$attempt" -ge "$DB_WAIT_ATTEMPTS" ]; then
			echo "ERROR: database ${DB_HOST} is unreachable after ${DB_WAIT_ATTEMPTS} attempts." >&2
			return 1
		fi

		attempt=$((attempt + 1))
		sleep 1
	done
}

cd "$APP_DIR"

# A restarted container keeps its filesystem, so a marker left behind by the
# previous run would report readiness before this one has finished.
rm -f "$READY_MARKER"

# The production image installs its dependencies at build time; in development
# the bind mount hides them, so they are installed on first start and whenever
# composer.lock has moved ahead of them.
if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
	run_step 'Installing PHP dependencies' composer install --no-interaction --no-scripts
else
	log_step 'PHP dependencies are up to date'
fi

if [ -n "${DB_HOST:-}" ]; then
	run_step "Waiting for database ${DB_HOST}" wait_for_database
fi

# Daily log rotation. Alpine's default root crontab already runs
# /etc/periodic/daily; crond just has to be alive to trigger it. It logs to
# syslog by default, which no container runs, so point it at stderr.
run_step 'Starting cron for log rotation' crond -b -L /dev/stderr

touch "$READY_MARKER"
log_step 'Startup complete, handing over to php-fpm'

exec php-fpm
