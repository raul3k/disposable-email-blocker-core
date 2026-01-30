<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Sources;

use InvalidArgumentException;
use Raul3k\BlockDisposable\Core\Parsers\JsonArrayParser;
use Raul3k\BlockDisposable\Core\Parsers\TextLineParser;

class SourceRegistry
{
    /** @var array<string, SourceInterface> */
    private array $sources = [];

    public function __construct()
    {
        $this->registerBuiltInSources();
    }

    /**
     * Get a source by name.
     */
    public function get(string $name): SourceInterface
    {
        if (!isset($this->sources[$name])) {
            throw new InvalidArgumentException(
                sprintf('Source "%s" not found. Available: %s', $name, implode(', ', $this->list()))
            );
        }

        return $this->sources[$name];
    }

    /**
     * Register a custom source.
     */
    public function register(SourceInterface $source): self
    {
        $this->sources[$source->getName()] = $source;

        return $this;
    }

    /**
     * Check if a source exists.
     */
    public function has(string $name): bool
    {
        return isset($this->sources[$name]);
    }

    /**
     * List all available source names.
     *
     * @return string[]
     */
    public function list(): array
    {
        return array_keys($this->sources);
    }

    /**
     * Get all registered sources.
     *
     * @return array<string, SourceInterface>
     */
    public function all(): array
    {
        return $this->sources;
    }

    /**
     * Remove a source by name.
     */
    public function remove(string $name): self
    {
        unset($this->sources[$name]);

        return $this;
    }

    private function registerBuiltInSources(): void
    {
        // disposable-email-domains - Large comprehensive list (~170k domains)
        // https://github.com/disposable-email-domains/disposable-email-domains
        $this->register(new UrlSource(
            url: 'https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/master/disposable_email_blocklist.conf',
            name: 'disposable-email-domains',
            parser: new TextLineParser()
        ));

        // burner-email-providers - Curated list (~4k domains)
        // https://github.com/wesbos/burner-email-providers
        $this->register(new UrlSource(
            url: 'https://raw.githubusercontent.com/wesbos/burner-email-providers/master/emails.txt',
            name: 'burner-email-providers',
            parser: new TextLineParser()
        ));

        // FGRibreau's mailchecker - Text format
        // https://github.com/FGRibreau/mailchecker
        $this->register(new UrlSource(
            url: 'https://raw.githubusercontent.com/FGRibreau/mailchecker/master/list.txt',
            name: 'mailchecker',
            parser: new TextLineParser()
        ));

        // Ivolo disposable email domains
        // https://github.com/ivolo/disposable-email-domains
        $this->register(new UrlSource(
            url: 'https://raw.githubusercontent.com/ivolo/disposable-email-domains/master/index.json',
            name: 'ivolo-disposable',
            parser: new JsonArrayParser()
        ));

        // 7c/fakefilter - Another comprehensive list
        // https://github.com/7c/fakefilter
        $this->register(new UrlSource(
            url: 'https://raw.githubusercontent.com/7c/fakefilter/main/txt/data.txt',
            name: 'fakefilter',
            parser: new TextLineParser()
        ));
    }
}
