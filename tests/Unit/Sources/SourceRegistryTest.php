<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests\Unit\Sources;

use InvalidArgumentException;
use Raul3k\BlockDisposable\Core\Sources\SourceInterface;
use Raul3k\BlockDisposable\Core\Sources\SourceRegistry;
use Raul3k\BlockDisposable\Core\Sources\UrlSource;
use Raul3k\BlockDisposable\Core\Tests\TestCase;

class SourceRegistryTest extends TestCase
{
    public function testHasBuiltInSources(): void
    {
        $registry = new SourceRegistry();

        $this->assertTrue($registry->has('disposable-email-domains'));
        $this->assertTrue($registry->has('burner-email-providers'));
        $this->assertTrue($registry->has('mailchecker'));
        $this->assertTrue($registry->has('ivolo-disposable'));
        $this->assertTrue($registry->has('fakefilter'));
    }

    public function testGetReturnsSource(): void
    {
        $registry = new SourceRegistry();

        $source = $registry->get('disposable-email-domains');

        $this->assertInstanceOf(SourceInterface::class, $source);
        $this->assertSame('disposable-email-domains', $source->getName());
    }

    public function testGetThrowsExceptionForUnknownSource(): void
    {
        $registry = new SourceRegistry();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source "unknown" not found');

        $registry->get('unknown');
    }

    public function testRegisterAddsSource(): void
    {
        $registry = new SourceRegistry();
        $source = new UrlSource('https://example.com/domains.txt', 'custom-source');

        $registry->register($source);

        $this->assertTrue($registry->has('custom-source'));
        $this->assertSame($source, $registry->get('custom-source'));
    }

    public function testRegisterReturnsFluentInterface(): void
    {
        $registry = new SourceRegistry();
        $source = new UrlSource('https://example.com/domains.txt', 'custom-source');

        $result = $registry->register($source);

        $this->assertSame($registry, $result);
    }

    public function testListReturnsAllSourceNames(): void
    {
        $registry = new SourceRegistry();

        $names = $registry->list();

        $this->assertContains('disposable-email-domains', $names);
        $this->assertContains('burner-email-providers', $names);
        $this->assertContains('mailchecker', $names);
    }

    public function testAllReturnsAllSources(): void
    {
        $registry = new SourceRegistry();

        $sources = $registry->all();

        $this->assertIsArray($sources);
        $this->assertArrayHasKey('disposable-email-domains', $sources);

        foreach ($sources as $source) {
            $this->assertInstanceOf(SourceInterface::class, $source);
        }
    }

    public function testRemoveDeletesSource(): void
    {
        $registry = new SourceRegistry();

        $this->assertTrue($registry->has('disposable-email-domains'));

        $registry->remove('disposable-email-domains');

        $this->assertFalse($registry->has('disposable-email-domains'));
    }

    public function testRemoveReturnsFluentInterface(): void
    {
        $registry = new SourceRegistry();

        $result = $registry->remove('disposable-email-domains');

        $this->assertSame($registry, $result);
    }

    public function testCanOverwriteExistingSource(): void
    {
        $registry = new SourceRegistry();
        $customSource = new UrlSource('https://custom.com/list.txt', 'disposable-email-domains');

        $registry->register($customSource);

        $this->assertSame($customSource, $registry->get('disposable-email-domains'));
        $this->assertSame('https://custom.com/list.txt', $registry->get('disposable-email-domains')->getUrl());
    }
}
