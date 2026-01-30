<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\DomainInfo;

class DomainInfoTest extends TestCase
{
    public function testParseSimpleDomain(): void
    {
        $info = DomainInfo::parse('example.com');

        $this->assertTrue($info->isValid());
        $this->assertEquals('example.com', $info->host());
        $this->assertEquals('example.com', $info->domain());
        $this->assertNull($info->subdomain());
        $this->assertEquals('com', $info->publicSuffix());
        $this->assertEquals('example', $info->secondLevelDomain());
        $this->assertTrue($info->isIcann());
        $this->assertFalse($info->isPrivate());
    }

    public function testParseWithSubdomain(): void
    {
        $info = DomainInfo::parse('mail.example.com');

        $this->assertEquals('mail.example.com', $info->host());
        $this->assertEquals('example.com', $info->domain());
        $this->assertEquals('mail', $info->subdomain());
        $this->assertEquals('com', $info->publicSuffix());
        $this->assertEquals('example', $info->secondLevelDomain());
    }

    public function testParseWithMultiPartTld(): void
    {
        $info = DomainInfo::parse('www.example.co.uk');

        $this->assertEquals('www.example.co.uk', $info->host());
        $this->assertEquals('example.co.uk', $info->domain());
        $this->assertEquals('www', $info->subdomain());
        $this->assertEquals('co.uk', $info->publicSuffix());
        $this->assertEquals('example', $info->secondLevelDomain());
        $this->assertTrue($info->isIcann());
    }

    public function testParseFromEmail(): void
    {
        $info = DomainInfo::parse('user@mail.example.com');

        $this->assertEquals('mail.example.com', $info->host());
        $this->assertEquals('example.com', $info->domain());
        $this->assertEquals('mail', $info->subdomain());
    }

    public function testParsePrivateSuffix(): void
    {
        $info = DomainInfo::parse('mysite.github.io');

        $this->assertEquals('mysite.github.io', $info->domain());
        $this->assertEquals('github.io', $info->publicSuffix());
        $this->assertEquals('mysite', $info->secondLevelDomain());
        $this->assertFalse($info->isIcann());
        $this->assertTrue($info->isPrivate());
    }

    public function testParseBlogspotPrivate(): void
    {
        $info = DomainInfo::parse('myblog.blogspot.com');

        $this->assertTrue($info->isPrivate());
        $this->assertFalse($info->isIcann());
        $this->assertEquals('blogspot.com', $info->publicSuffix());
    }

    public function testParseWithUrl(): void
    {
        $info = DomainInfo::parse('https://www.example.com/path/to/page');

        $this->assertEquals('www.example.com', $info->host());
        $this->assertEquals('example.com', $info->domain());
    }

    public function testParseWithPort(): void
    {
        $info = DomainInfo::parse('example.com:8080');

        $this->assertEquals('example.com', $info->host());
        $this->assertEquals('example.com', $info->domain());
    }

    public function testParseInvalidInput(): void
    {
        $info = DomainInfo::parse('not-a-valid-tld');

        $this->assertFalse($info->isValid());
        $this->assertNull($info->domain());
    }

    public function testParseEmptyInput(): void
    {
        $info = DomainInfo::parse('');

        $this->assertFalse($info->isValid());
        $this->assertNull($info->domain());
        $this->assertEquals('', $info->originalInput());
    }

    public function testAliases(): void
    {
        $info = DomainInfo::parse('example.co.uk');

        // tld() is alias for publicSuffix()
        $this->assertEquals($info->publicSuffix(), $info->tld());

        // sld() is alias for secondLevelDomain()
        $this->assertEquals($info->secondLevelDomain(), $info->sld());

        // registrableDomain() is alias for domain()
        $this->assertEquals($info->domain(), $info->registrableDomain());
    }

    public function testToArray(): void
    {
        $info = DomainInfo::parse('mail.example.com');
        $array = $info->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('mail.example.com', $array['original_input']);
        $this->assertEquals('mail.example.com', $array['host']);
        $this->assertEquals('example.com', $array['domain']);
        $this->assertEquals('mail', $array['subdomain']);
        $this->assertEquals('com', $array['public_suffix']);
        $this->assertEquals('example', $array['second_level_domain']);
        $this->assertTrue($array['is_icann']);
        $this->assertFalse($array['is_private']);
        $this->assertTrue($array['is_valid']);
    }

    public function testToJson(): void
    {
        $info = DomainInfo::parse('example.com');
        $json = $info->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('example.com', $decoded['domain']);
    }

    public function testIsKnownSuffix(): void
    {
        $known = DomainInfo::parse('example.com');
        $this->assertTrue($known->isKnownSuffix());

        $private = DomainInfo::parse('mysite.github.io');
        $this->assertTrue($private->isKnownSuffix());
    }

    #[DataProvider('icannDomainsProvider')]
    public function testIcannDomains(string $input, string $expectedDomain, string $expectedSuffix): void
    {
        $info = DomainInfo::parse($input);

        $this->assertTrue($info->isIcann());
        $this->assertFalse($info->isPrivate());
        $this->assertEquals($expectedDomain, $info->domain());
        $this->assertEquals($expectedSuffix, $info->publicSuffix());
    }

    public static function icannDomainsProvider(): array
    {
        return [
            ['google.com', 'google.com', 'com'],
            ['example.org', 'example.org', 'org'],
            ['test.net', 'test.net', 'net'],
            ['site.com.br', 'site.com.br', 'com.br'],
            ['example.co.jp', 'example.co.jp', 'co.jp'],
        ];
    }

    #[DataProvider('privateDomainsProvider')]
    public function testPrivateDomains(string $input): void
    {
        $info = DomainInfo::parse($input);

        $this->assertTrue($info->isPrivate());
        $this->assertFalse($info->isIcann());
    }

    public static function privateDomainsProvider(): array
    {
        return [
            ['mysite.github.io'],
            ['myblog.blogspot.com'],
            ['myapp.herokuapp.com'],
            ['bucket.s3.amazonaws.com'],
        ];
    }
}
