<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Checkers;

use Raul3k\BlockDisposable\Core\Checkers\FileChecker;
use Raul3k\BlockDisposable\Core\Tests\TestCase;
use RuntimeException;

class FileCheckerTest extends TestCase
{
    private ?string $tempFile = null;

    protected function tearDown(): void
    {
        if ($this->tempFile !== null) {
            $this->removeTempFile($this->tempFile);
            $this->tempFile = null;
        }
    }

    public function testIsDomainDisposableReturnsTrueForDisposableDomain(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nguerrillamail.com\ntemp-mail.org");

        $checker = new FileChecker($this->tempFile);

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertTrue($checker->isDomainDisposable('guerrillamail.com'));
        $this->assertTrue($checker->isDomainDisposable('temp-mail.org'));
    }

    public function testIsDomainDisposableReturnsFalseForNonDisposableDomain(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nguerrillamail.com");

        $checker = new FileChecker($this->tempFile);

        $this->assertFalse($checker->isDomainDisposable('gmail.com'));
        $this->assertFalse($checker->isDomainDisposable('example.com'));
    }

    public function testIgnoresCommentLines(): void
    {
        $this->tempFile = $this->createTempFile("# This is a comment\nmailinator.com\n# Another comment");

        $checker = new FileChecker($this->tempFile);

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertFalse($checker->isDomainDisposable('# This is a comment'));
    }

    public function testIgnoresEmptyLines(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\n\n\nguerrillamail.com\n");

        $checker = new FileChecker($this->tempFile);

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertTrue($checker->isDomainDisposable('guerrillamail.com'));
        $this->assertFalse($checker->isDomainDisposable(''));
    }

    public function testTrimsWhitespace(): void
    {
        $this->tempFile = $this->createTempFile("  mailinator.com  \n\tguerrillamail.com\t");

        $checker = new FileChecker($this->tempFile);

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));
        $this->assertTrue($checker->isDomainDisposable('guerrillamail.com'));
    }

    public function testLazyLoading(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = new FileChecker($this->tempFile);

        // File content can be modified before first check
        file_put_contents($this->tempFile, 'newdomain.com');

        $this->assertTrue($checker->isDomainDisposable('newdomain.com'));
        $this->assertFalse($checker->isDomainDisposable('mailinator.com'));
    }

    public function testReloadRefreshesCache(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');

        $checker = new FileChecker($this->tempFile);

        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));

        file_put_contents($this->tempFile, 'newdomain.com');

        // Before reload, old data is still cached
        $this->assertTrue($checker->isDomainDisposable('mailinator.com'));

        $checker->reload();

        // After reload, new data is loaded
        $this->assertTrue($checker->isDomainDisposable('newdomain.com'));
        $this->assertFalse($checker->isDomainDisposable('mailinator.com'));
    }

    public function testCountReturnsDomainCount(): void
    {
        $this->tempFile = $this->createTempFile("mailinator.com\nguerrillamail.com\ntemp-mail.org");

        $checker = new FileChecker($this->tempFile);

        $this->assertSame(3, $checker->count());
    }

    public function testThrowsExceptionForNonExistentFile(): void
    {
        $checker = new FileChecker('/non/existent/file.txt');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read domains file');

        $checker->isDomainDisposable('test.com');
    }

    public function testThrowsExceptionForUnreadableFile(): void
    {
        $this->tempFile = $this->createTempFile('mailinator.com');
        chmod($this->tempFile, 0000);

        $checker = new FileChecker($this->tempFile);

        $this->expectException(RuntimeException::class);

        try {
            $checker->isDomainDisposable('test.com');
        } finally {
            chmod($this->tempFile, 0644);
        }
    }
}
