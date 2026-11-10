<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class ExcludedPathChecker
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * Resolved exclude patterns, keyed by the checker-specific extra excludes
     *
     * @var array<string, string[]>
     */
    private $resolvedPatterns = [];

    /**
     * @param DirectoryList $directoryList
     * @param Config $config
     * @param File $fileDriver
     */
    public function __construct(
        DirectoryList $directoryList,
        Config $config,
        File $fileDriver
    ) {
        $this->directoryList = $directoryList;
        $this->config = $config;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Decide whether an absolute file path is excluded from scanning.
     *
     * @param string $absolutePath absolute path of the scanned file
     * @param string[] $extraExcludes checker-specific excludes, relative to root or absolute
     * @return bool
     */
    public function isExcluded(string $absolutePath, array $extraExcludes = []): bool
    {
        $absolutePath = $this->fileDriver->getRealPath($absolutePath);
        if ($absolutePath === false) {
            return false;
        }

        return $this->isExcludedRealPath($absolutePath, $extraExcludes);
    }

    /**
     * Same as isExcluded() for a path that is already resolved, without another realpath() call
     *
     * Use it for paths from a RecursiveDirectoryIterator started at a real path:
     * the iterator does not descend into symlinked directories, so such paths are already real.
     *
     * @param string $realPath
     * @param string[] $extraExcludes
     * @return bool
     */
    public function isExcludedRealPath(string $realPath, array $extraExcludes = []): bool
    {
        foreach ($this->getResolvedPatterns($extraExcludes) as $pattern) {
            /** Exact file match, or directory-prefix match. */
            if ($realPath === $pattern || strpos($realPath, $pattern . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get excluded paths resolved to real absolute paths (non-existing entries are skipped)
     *
     * @param string[] $extraExcludes
     * @return string[]
     */
    public function getExcludedRealPaths(array $extraExcludes = []): array
    {
        return $this->getResolvedPatterns($extraExcludes);
    }

    /**
     * Resolve exclude patterns to real paths once per extra-excludes set
     *
     * Scans call isExcluded() for every file, so resolving the patterns on each call
     * means several realpath() lookups per file (slow on stores with millions of media files).
     *
     * @param string[] $extraExcludes
     * @return string[]
     */
    private function getResolvedPatterns(array $extraExcludes): array
    {
        $key = implode("\n", $extraExcludes);
        if (isset($this->resolvedPatterns[$key])) {
            return $this->resolvedPatterns[$key];
        }

        $resolved = [];
        foreach (array_merge($extraExcludes, $this->config->getFileScanExcludedPaths()) as $pattern) {
            $pattern = trim((string)$pattern);
            if ($pattern === '') {
                continue;
            }
            /** Relative entries are anchored to the Magento root; non-existing paths are skipped. */
            $rootPath = $this->directoryList->getRoot() . '/' . ltrim($pattern, '/');
            $realPath = $this->fileDriver->getRealPath($rootPath) ?: $this->fileDriver->getRealPath($pattern);
            if ($realPath !== false) {
                $resolved[] = $realPath;
            }
        }

        return $this->resolvedPatterns[$key] = $resolved;
    }
}
