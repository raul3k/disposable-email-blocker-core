<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Config;

use Raul3k\BlockDisposable\Core\Sources\SourceInterface;
use Raul3k\BlockDisposable\Core\Sources\SourceRegistry;

/**
 * Configuration loader for custom sources.
 *
 * Users can create a configuration file in their project root:
 * - disposable-blocker.php
 * - .disposable-blocker.php
 * - config/disposable-blocker.php
 *
 * The file should return an array with configuration options.
 *
 * @example
 * ```php
 * // disposable-blocker.php
 * return [
 *     'sources' => [
 *         new UrlSource('https://example.com/domains.txt', 'my-source'),
 *         new FileSource('/path/to/domains.txt', 'local-source'),
 *     ],
 *     'exclude_sources' => ['fakefilter'], // Exclude built-in sources
 *     'output_path' => 'storage/disposable_domains.txt', // Custom output
 * ];
 * ```
 */
class Configuration
{
    private const CONFIG_FILES = [
        'disposable-blocker.php',
        '.disposable-blocker.php',
        'config/disposable-blocker.php',
    ];

    /** @var array<SourceInterface> */
    private array $sources = [];

    /** @var array<string> */
    private array $excludeSources = [];

    private ?string $outputPath = null;

    private ?string $configPath = null;

    public function __construct(?string $projectRoot = null)
    {
        $projectRoot ??= $this->findProjectRoot();

        if ($projectRoot !== null) {
            $this->loadFromProjectRoot($projectRoot);
        }
    }

    /**
     * Load configuration from a specific file.
     */
    public static function fromFile(string $path): self
    {
        $config = new self(null);
        $config->loadFile($path);

        return $config;
    }

    /**
     * Apply configuration to a SourceRegistry.
     */
    public function applyTo(SourceRegistry $registry): void
    {
        // Remove excluded sources
        foreach ($this->excludeSources as $name) {
            $registry->remove($name);
        }

        // Add custom sources
        foreach ($this->sources as $source) {
            $registry->register($source);
        }
    }

    /**
     * Get custom sources.
     *
     * @return array<SourceInterface>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Get sources to exclude.
     *
     * @return array<string>
     */
    public function getExcludeSources(): array
    {
        return $this->excludeSources;
    }

    /**
     * Get custom output path.
     */
    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    /**
     * Get the loaded config file path.
     */
    public function getConfigPath(): ?string
    {
        return $this->configPath;
    }

    /**
     * Check if a config file was loaded.
     */
    public function isLoaded(): bool
    {
        return $this->configPath !== null;
    }

    private function loadFromProjectRoot(string $projectRoot): void
    {
        foreach (self::CONFIG_FILES as $file) {
            $path = $projectRoot . '/' . $file;
            if (is_file($path)) {
                $this->loadFile($path);

                return;
            }
        }
    }

    private function loadFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException("Cannot read config file: $path");
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new \RuntimeException("Config file must return an array: $path");
        }

        $this->configPath = $path;

        if (isset($config['sources']) && is_array($config['sources'])) {
            foreach ($config['sources'] as $source) {
                if ($source instanceof SourceInterface) {
                    $this->sources[] = $source;
                }
            }
        }

        if (isset($config['exclude_sources']) && is_array($config['exclude_sources'])) {
            $this->excludeSources = array_filter($config['exclude_sources'], 'is_string');
        }

        if (isset($config['output_path']) && is_string($config['output_path'])) {
            $this->outputPath = $config['output_path'];
        }
    }

    private function findProjectRoot(): ?string
    {
        // Start from vendor directory and go up
        $dir = __DIR__;

        // If we're in vendor/raul3k/disposable-email-blocker-core/src/Config
        // we need to go up 5 levels to reach project root
        for ($i = 0; $i < 10; $i++) {
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;

            // Check if this looks like a project root
            if (is_file($dir . '/composer.json') && !is_file($dir . '/vendor/autoload.php')) {
                continue;
            }

            if (is_file($dir . '/composer.json') && is_dir($dir . '/vendor')) {
                return $dir;
            }
        }

        // Fallback: use current working directory
        $cwd = getcwd();
        if ($cwd !== false && is_file($cwd . '/composer.json')) {
            return $cwd;
        }

        return null;
    }
}
