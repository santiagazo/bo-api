#!/bin/sh
set -e

echo "Starting Built Ox API..."

# App Runner injects the database secret as one JSON value. Convert the
# allow-listed keys into the environment variables Laravel expects before
# caching configuration. Values are shell-escaped by PHP before evaluation.
if [ -n "${DB_SECRET_JSON:-}" ]; then
    eval "$(
        php -r '
            $secret = json_decode((string) getenv("DB_SECRET_JSON"), true, 512, JSON_THROW_ON_ERROR);
            $keys = ["DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD"];

            foreach ($keys as $key) {
                if (! array_key_exists($key, $secret)) {
                    fwrite(STDERR, "Database secret is missing {$key}.\n");
                    exit(1);
                }

                printf("export %s=%s\n", $key, escapeshellarg((string) $secret[$key]));
            }
        '
    )"
    unset DB_SECRET_JSON
fi

# Create nginx temp directories
mkdir -p /tmp/nginx/client_body /tmp/nginx/proxy /tmp/nginx/fastcgi /tmp/nginx/uwsgi /tmp/nginx/scgi

# Run Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

