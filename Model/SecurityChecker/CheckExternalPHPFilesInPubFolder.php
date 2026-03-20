<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magento\Framework\Exception\FileSystemException;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CheckExternalPHPFilesInPubFolder extends AbstractChecker
{

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $file;

    /**
     * @var mixed|null
     */
    private $position;

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param DirectoryList $directoryList
     * @param File $file
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param Json $json
     * @param mixed $position
     */
    public function __construct(
        DirectoryList              $directoryList,
        File                       $file,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        Json                       $json,
        $position = null
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->json = $json;
        $this->position = $position;
        parent::__construct($securityStatusCacheFactory);
    }

    /**
     * Check if issue exist
     *
     * @return int
     */
    public function issueExists()
    {
        $this->loadCache();
        return $this->issueExists;
    }

    /**
     * Update cache
     *
     * @return CheckExternalPHPFilesInPubFolder
     * @throws FileSystemException
     */
    public function updateCache()
    {
        $allowedPubFiles = ['cron.php', 'get.php', 'health_check.php', 'index.php', 'static.php'];
        $allowedPubSubfolderFiles = [
            'errors/404.php',
            'errors/503.php',
            'errors/noCache.php',
            'errors/processor.php',
            'errors/processorFactory.php',
            'errors/report.php',
        ];
        $pubFolder = $this->directoryList->getPath('pub');
        $externalFiles = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pubFolder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $fileInfo = $this->file->getPathInfo($file->getPathName());

            if (!isset($fileInfo['extension']) || $fileInfo['extension'] !== 'php') {
                continue;
            }

            $relativePath = ltrim(str_replace($pubFolder, '', $file->getPathName()), '/\\');

            if ($iterator->getDepth() === 0) {
                // Files directly in pub/ are checked against the root allowed list
                if (!in_array($file->getFileName(), $allowedPubFiles)) {
                    $externalFiles[] = $relativePath;
                }
            } elseif (!in_array($relativePath, $allowedPubSubfolderFiles)) {
                // PHP files in subdirectories are flagged unless they are default Magento files
                $externalFiles[] = $relativePath;
            }
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                count($externalFiles)
                    ? SecurityCheckerInterface::NOTICE
                    : SecurityCheckerInterface::OK
            )
            ->setDetails($this->json->serialize($externalFiles))
            ->save();

        $this->cacheLoaded = false;

        return $this;
    }

    /**
     *  Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Unauthorized custom files');
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckExternalPHPFilesInPubFolder';
    }

    /**
     * Get type
     *
     * @return int
     * @throws FileSystemException
     */
    public function getType(): int
    {
        return (int)$this->issueExists();
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return (int)$this->position;
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getDetails(): array
    {
        if (!is_array($this->details)) {
            $this->details = $this->json->unserialize($this->details);
        }

        return $this->details;
    }

    /**
     * Get suggestions
     *
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__('Identify suspicious or unknown files that may indicate a security breach.')
            : $this->getResolvedMessage();
    }
}
