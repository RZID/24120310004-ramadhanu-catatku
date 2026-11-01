#!/bin/sh
# Starts nginx in front of php-fpm, both as the unprivileged www-data user.
set -eu

# `compose run app php artisan migrate` must keep working, so an explicit command
# always wins over the web server.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# nginx has no environment expansion, so the template is rendered once at start-up.
# It goes to /tmp because /etc is root owned and this process is www-data.
sed "s/__PORT__/${PORT:-8080}/g" /etc/nginx/nginx.conf.template > /tmp/nginx.conf

/usr/local/sbin/php-fpm --nodaemonize &
fpm=$!

/usr/sbin/nginx -c /tmp/nginx.conf -g 'daemon off;' &
web=$!

# If either process dies the container must die too, otherwise the health check
# keeps passing while every request returns 502.
trap 'kill -QUIT "$fpm" "$web" 2>/dev/null || true' TERM INT

while kill -0 "$fpm" 2>/dev/null && kill -0 "$web" 2>/dev/null; do
    sleep 1
done

kill -QUIT "$fpm" "$web" 2>/dev/null || true
wait 2>/dev/null || true
exit 1
