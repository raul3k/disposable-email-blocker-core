<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Cache;

use RuntimeException;

/**
 * File-based cache implementation.
 *
 * Stores cached values as serialized PHP in files.
 * Suitable for simple caching needs without external dependencies.
 */
class FileCache implements CacheInterface
{
    private string $directory;

    /**
     * @param string $directory The directory to store cache files
     * @throws RuntimeException If directory cannot be created or is not writable
     */
    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);

        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
                throw new RuntimeException(
                    sprintf('Cache directory "%s" could not be created', $this->directory)
                );
            }
        }

        if (!is_writable($this->directory)) {
            throw new RuntimeException(
                sprintf('Cache directory "%s" is not writable', $this->directory)
            );
        }
    }

    public function get(string $key): mixed
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $data = unserialize($content);
        if (!is_array($data) || !isset($data['value'])) {
            return null;
        }

        if (isset($data['expires']) && $data['expires'] < time()) {
            $this->delete($key);

            return null;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $path = $this->getPath($key);

        $data = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
            'created' => time(),
        ];

        $content = serialize($data);
        $written = file_put_contents($path, $content, LOCK_EX);

        return $written !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        $path = $this->getPath($key);

        if (!file_exists($path)) {
            return true;
        }

        return unlink($path);
    }

    public function clear(): bool
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.cache');
        if ($files === false) {
            return false;
        }

        $success = true;
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get the cache file path for a key.
     */
    private function getPath(string $key): string
    {
        $hash = md5($key);

        return $this->directory . DIRECTORY_SEPARATOR . $hash . '.cache';
    }

    /**
     * Remove all expired cache entries.
     */
    public function gc(): int
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.cache');
        if ($files === false) {
            return 0;
        }

        $removed = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = unserialize($content);
            if (is_array($data) && isset($data['expires']) && $data['expires'] < time()) {
                if (unlink($file)) {
                    $removed++;
                }
            }
        }

        return $removed;
    }
}
