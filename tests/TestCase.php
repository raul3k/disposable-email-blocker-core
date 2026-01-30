<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getFixturePath(string $filename): string
    {
        return __DIR__ . '/Fixtures/' . $filename;
    }

    protected function createTempFile(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'disposable_test_');
        file_put_contents($path, $content);

        return $path;
    }

    protected function removeTempFile(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
