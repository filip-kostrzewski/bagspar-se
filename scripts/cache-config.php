<?php

$root = dirname(__DIR__);
$appEnv = getenv('APP_ENV');
$appEnv = is_string($appEnv) ? trim($appEnv) : '';

if ($appEnv === '') {
    $envPath = $root.DIRECTORY_SEPARATOR.'.env';
    $contents = is_file($envPath) ? file_get_contents($envPath) : false;

    if (is_string($contents) && preg_match('/^APP_ENV=(.*)$/m', $contents, $matches) === 1) {
        $appEnv = trim($matches[1], " \t\"'");
    }
}

$onForge = getenv('FORGE_SITE_BRANCH') !== false || getenv('FORGE_PHP') !== false;

if ($appEnv !== 'production' && ! $onForge) {
    fwrite(STDOUT, "Config cache left unchanged (not a production deploy).\n");

    exit(0);
}

$run = static function (array $arguments) use ($root): int {
    $command = array_merge([PHP_BINARY], $arguments);
    $process = proc_open($command, [
        0 => ['pipe', 'r'],
        1 => STDOUT,
        2 => STDERR,
    ], $pipes, $root);

    if (! is_resource($process)) {
        fwrite(STDERR, 'Unable to run '.implode(' ', $command).PHP_EOL);

        return 1;
    }

    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    return proc_close($process);
};

foreach ([
    ['artisan', 'config:clear', '--no-interaction'],
    ['artisan', 'config:cache', '--no-interaction'],
] as $arguments) {
    $exitCode = $run($arguments);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}
