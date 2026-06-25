<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magento\Framework\Filesystem\DirectoryList;

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
     * @param DirectoryList $directoryList
     * @param Config $config
     */
    public function __construct(
        DirectoryList $directoryList,
        Config $config
    ) {
        $this->directoryList = $directoryList;
        $this->config = $config;
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
        $absolutePath = realpath($absolutePath);
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
            $pattern = realpath($this->directoryList->getRoot() . '/' . ltrim($pattern, '/'))
                ?: realpath($pattern);
            if ($pattern === false) {
                continue;
            }
            /** Exact file match, or directory-prefix match. */
            if ($absolutePath === $pattern || str_starts_with($absolutePath, $pattern . '/')) {
                return true;
            }
        }

        return false;
    }
}
