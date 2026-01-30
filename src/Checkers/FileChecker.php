<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

use RuntimeException;

class FileChecker implements CheckerInterface
{
    private ?array $domains = null;

    public function __construct(
        private readonly string $filePath
    ) {
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        $this->loadDomains();

        return isset($this->domains[$normalizedDomain]);
    }

    /**
     * Reload domains from file. Useful for long-running processes.
     */
    public function reload(): void
    {
        $this->domains = null;
        $this->loadDomains();
    }

    /**
     * Get the number of loaded domains.
     */
    public function count(): int
    {
        $this->loadDomains();

        return count($this->domains ?? []);
    }

    /**
     * Get the file path.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    private function loadDomains(): void
    {
        if ($this->domains !== null) {
            return;
        }

        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException(
                sprintf('Cannot read domains file: %s', $this->filePath)
            );
        }

        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException(
                sprintf('Failed to open domains file: %s', $this->filePath)
            );
        }

        $domains = [];

        while (($line = fgets($handle)) !== false) {
            $domain = trim($line);
            if ($domain !== '' && $domain[0] !== '#') {
                $domains[$domain] = true;
            }
        }

        fclose($handle);
        $this->domains = $domains;
    }
}
