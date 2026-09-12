<?php
/**
 * Minimal .env reader.
 *
 * Yii does not load .env by itself and this template deliberately has no extra
 * dependency for it, so config/db.php reads its values through this helper.
 *
 * Real environment variables win, so a deployment can inject credentials without
 * writing files. Otherwise the .env file is used — .env locally, and .env.prod
 * copied over it by docker-entrypoint.sh once deployed.
 *
 * "" counts as not set: an empty DB_HOST is not a usable host, and treating it as
 * configured only produces a confusing connection error later.
 */

if (! function_exists('ws_env')) {
    function ws_env(string $key): ?string
    {
        static $file = null;

        $value = getenv($key);
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if ($file === null) {
            $file = [];
            $path = dirname(__DIR__) . '/.env';

            if (is_readable($path)) {
                foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#' || ! str_contains($line, '=')) {
                        continue;
                    }

                    [$name, $raw] = explode('=', $line, 2);
                    $raw = trim($raw);
                    // Values may be quoted; a password is free to contain '#', so
                    // only strip quotes, never anything inside them.
                    if (strlen($raw) >= 2 && ($raw[0] === '"' || $raw[0] === "'") && $raw[-1] === $raw[0]) {
                        $raw = substr($raw, 1, -1);
                    }
                    $file[trim($name)] = $raw;
                }
            }
        }

        $value = $file[$key] ?? '';

        return $value === '' ? null : $value;
    }
}
