<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ComposerDeployScriptsTest extends TestCase
{
    public function test_composer_scripts_do_not_inline_shell_variables(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);
        $commands = [];

        array_walk_recursive($composer['scripts'], function (mixed $value) use (&$commands): void {
            if (is_string($value)) {
                $commands[] = $value;
            }
        });

        $this->assertSame(['@php scripts/cache-config.php'], $composer['scripts']['post-install-cmd']);

        foreach ($commands as $command) {
            $this->assertDoesNotMatchRegularExpression('/\$[A-Za-z_]/', $command);
        }
    }
}
