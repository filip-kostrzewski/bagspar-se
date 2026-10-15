<?php

$root = dirname(__DIR__);
$appEnv = getenv('APP_ENV');
$appEnv = is_string($appEnv) ? $appEnv : '';

if ($appEnv === '') {
    $envFile = $root.'/.env';
    $contents = is_file($envFile) ? file_get_contents($envFile) : false;

    if (is_string($contents) && preg_match('/^APP_ENV=(.*)$/m', $contents, $matches) === 1) {
        $appEnv = trim($matches[1], " \t\"'");
    }
}

$onForge = getenv('FORGE_SITE_BRANCH') !== false || getenv('FORGE_PHP') !== false;

if ($appEnv !== 'production' && ! $onForge) {
    fwrite(STDOUT, "Config cache left unchanged (not a production deploy).\n");

    exit(0);
}

foreach ([
    PHP_BINARY.' artisan config:clear --no-interaction',
    PHP_BINARY.' artisan config:cache --no-interaction',
] as $command) {
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}
