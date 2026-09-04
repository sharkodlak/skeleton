#!/bin/sh
# nginx resolves a literal upstream name once, when it loads its configuration,
# and then keeps that address forever. If the PHP container is recreated with a
# different address, every request fails with 502 until nginx is reloaded.
#
# Resolving through a variable avoids that, but it needs a resolver, and the
# resolver's address differs between runtimes: Docker uses 127.0.0.11, Podman
# publishes the gateway. Read it from the container's own resolv.conf so the
# image works on both, and let an explicit DNS_RESOLVER win.
set -e

if [ -z "${DNS_RESOLVER:-}" ]; then
	DNS_RESOLVER=$(awk '/^nameserver/ { print $2; exit }' /etc/resolv.conf 2>/dev/null || true)
fi

: "${DNS_RESOLVER:=127.0.0.11}"
export DNS_RESOLVER

echo "Using DNS resolver: ${DNS_RESOLVER}"

exec /docker-entrypoint.sh "$@"
