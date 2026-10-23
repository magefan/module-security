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

        $patterns = array_merge($extraExcludes, $this->config->getFileScanExcludedPaths());

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if ($pattern === '') {
                continue;
            }
            /** Relative entries are anchored to the Magento root; non-existing paths are skipped. */
            $rootPath = $this->directoryList->getRoot() . '/' . ltrim($pattern, '/');
            $pattern = $this->fileDriver->getRealPath($rootPath) ?: $this->fileDriver->getRealPath($pattern);
            if ($pattern === false) {
                continue;
            }
            /** Exact file match, or directory-prefix match. */
            if ($absolutePath === $pattern || strpos($absolutePath, $pattern . '/') === 0) {
                return true;
            }
        }

        return false;
    }
}
