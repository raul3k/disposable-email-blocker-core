<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Tests;

use PHPUnit\Framework\TestCase;
use Raul3k\BlockDisposable\Core\CheckResult;

class CheckResultTest extends TestCase
{
    public function testDisposableFactory(): void
    {
        $result = CheckResult::disposable('mailinator.com', 'test@mailinator.com', 'FileChecker');

        $this->assertTrue($result->isDisposable());
        $this->assertFalse($result->isSafe());
        $this->assertFalse($result->isWhitelisted());
        $this->assertEquals('mailinator.com', $result->getDomain());
        $this->assertEquals('test@mailinator.com', $result->getOriginalInput());
        $this->assertEquals('FileChecker', $result->getMatchedChecker());
        $this->assertEquals(CheckResult::CONFIDENCE_HIGH, $result->getConfidence());
    }

    public function testSafeFactory(): void
    {
        $result = CheckResult::safe('gmail.com', 'test@gmail.com');

        $this->assertFalse($result->isDisposable());
        $this->assertTrue($result->isSafe());
        $this->assertFalse($result->isWhitelisted());
        $this->assertEquals('gmail.com', $result->getDomain());
        $this->assertNull($result->getMatchedChecker());
    }

    public function testWhitelistedFactory(): void
    {
        $result = CheckResult::whitelisted('company.com', 'test@company.com');

        $this->assertFalse($result->isDisposable());
        $this->assertTrue($result->isSafe());
        $this->assertTrue($result->isWhitelisted());
        $this->assertEquals('company.com', $result->getDomain());
    }

    public function testConfidenceLevels(): void
    {
        $highConfidence = CheckResult::disposable('domain.com', 'test@domain.com', 'Checker', CheckResult::CONFIDENCE_HIGH);
        $mediumConfidence = CheckResult::disposable('domain.com', 'test@domain.com', 'Checker', CheckResult::CONFIDENCE_MEDIUM);
        $lowConfidence = CheckResult::disposable('domain.com', 'test@domain.com', 'Checker', CheckResult::CONFIDENCE_LOW);

        $this->assertTrue($highConfidence->isHighConfidence());
        $this->assertFalse($mediumConfidence->isHighConfidence());
        $this->assertFalse($lowConfidence->isHighConfidence());
    }

    public function testToArray(): void
    {
        $result = CheckResult::disposable('mailinator.com', 'test@mailinator.com', 'FileChecker');
        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('mailinator.com', $array['domain']);
        $this->assertEquals('test@mailinator.com', $array['original_input']);
        $this->assertTrue($array['is_disposable']);
        $this->assertFalse($array['is_safe']);
        $this->assertEquals('FileChecker', $array['matched_checker']);
        $this->assertFalse($array['is_whitelisted']);
        $this->assertEquals(1.0, $array['confidence']);
    }

    public function testToJson(): void
    {
        $result = CheckResult::safe('gmail.com', 'test@gmail.com');
        $json = $result->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals('gmail.com', $decoded['domain']);
        $this->assertFalse($decoded['is_disposable']);
    }
}
