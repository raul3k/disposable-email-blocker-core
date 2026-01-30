<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core;

use Closure;
use Raul3k\BlockDisposable\Core\Cache\CacheInterface;
use Raul3k\BlockDisposable\Core\Cache\FileCache;
use Raul3k\BlockDisposable\Core\Checkers\CachedChecker;
use Raul3k\BlockDisposable\Core\Checkers\CallbackChecker;
use Raul3k\BlockDisposable\Core\Checkers\ChainChecker;
use Raul3k\BlockDisposable\Core\Checkers\CheckerInterface;
use Raul3k\BlockDisposable\Core\Checkers\FileChecker;
use Raul3k\BlockDisposable\Core\Checkers\PatternChecker;
use Raul3k\BlockDisposable\Core\Checkers\WhitelistChecker;

/**
 * Fluent builder for DisposableEmailChecker.
 *
 * @example
 * $checker = DisposableEmailChecker::builder()
 *     ->withBundledDomains()
 *     ->withPatternDetection()
 *     ->withWhitelist(['mycompany.com'])
 *     ->withFileCache('/tmp/cache')
 *     ->build();
 */
class DisposableEmailCheckerBuilder
{
    /** @var array<CheckerInterface> */
    private array $checkers = [];

    /** @var array<string> */
    private array $whitelist = [];

    private ?CacheInterface $cache = null;

    private ?int $cacheTtl = null;

    private ?DomainNormalizer $normalizer = null;

    /**
     * Use the bundled disposable domains list.
     */
    public function withBundledDomains(): self
    {
        $this->checkers[] = new FileChecker(
            __DIR__ . '/Resources/disposable_domains.txt'
        );

        return $this;
    }

    /**
     * Use a custom domains file.
     */
    public function withDomainsFile(string $path): self
    {
        $this->checkers[] = new FileChecker($path);

        return $this;
    }

    /**
     * Enable pattern-based detection.
     *
     * @param array<string>|null $patterns Custom patterns (null = use defaults)
     */
    public function withPatternDetection(?array $patterns = null): self
    {
        $this->checkers[] = new PatternChecker($patterns);

        return $this;
    }

    /**
     * Add a custom checker.
     */
    public function withChecker(CheckerInterface $checker): self
    {
        $this->checkers[] = $checker;

        return $this;
    }

    /**
     * Add a callback-based checker.
     *
     * @param callable(string): bool $callback
     */
    public function withCallback(callable $callback): self
    {
        $closure = $callback instanceof Closure
            ? $callback
            : Closure::fromCallable($callback);

        $this->checkers[] = new CallbackChecker($closure);

        return $this;
    }

    /**
     * Add domains to the whitelist.
     *
     * @param array<string> $domains
     */
    public function withWhitelist(array $domains): self
    {
        $this->whitelist = array_merge($this->whitelist, $domains);

        return $this;
    }

    /**
     * Enable file-based caching.
     */
    public function withFileCache(string $directory, ?int $ttl = 3600): self
    {
        $this->cache = new FileCache($directory);
        $this->cacheTtl = $ttl;

        return $this;
    }

    /**
     * Enable caching with a custom cache implementation.
     */
    public function withCache(CacheInterface $cache, ?int $ttl = 3600): self
    {
        $this->cache = $cache;
        $this->cacheTtl = $ttl;

        return $this;
    }

    /**
     * Use a custom domain normalizer.
     */
    public function withNormalizer(DomainNormalizer $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * Build the checker instance.
     */
    public function build(): DisposableEmailChecker
    {
        if (empty($this->checkers)) {
            $this->withBundledDomains();
        }

        // Combine checkers
        $checker = count($this->checkers) === 1
            ? $this->checkers[0]
            : new ChainChecker($this->checkers);

        // Apply whitelist
        if (!empty($this->whitelist)) {
            $checker = new WhitelistChecker($checker, $this->whitelist);
        }

        // Apply cache
        if ($this->cache !== null) {
            $checker = new CachedChecker($checker, $this->cache, $this->cacheTtl);
        }

        $normalizer = $this->normalizer ?? new DomainNormalizer();

        return new DisposableEmailChecker($checker, $normalizer);
    }
}
