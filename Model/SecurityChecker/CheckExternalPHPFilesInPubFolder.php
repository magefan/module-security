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
use DirectoryIterator;

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
     * @var
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
     * @param $position
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
     * @return int
     */
    public function issueExists()
    {
        $this->loadCache();
        return $this->issueExists;
    }

    /**
     * @return CheckExternalPHPFilesInPubFolder
     * @throws FileSystemException
     */
    public function updateCache()
    {
        $allowedPubFiles = ['cron.php', 'get.php', 'health_check.php', 'index.php', 'static.php'];
        $pubFolder = $this->directoryList->getPath('pub');
        $externalFiles = [];

        foreach (new DirectoryIterator($pubFolder) as $file) {
            $fileInfo = $this->file->getPathInfo($file->getPathName());

            if ($file->isFile() && isset($fileInfo['extension']) && ($fileInfo['extension'] == "php")) {
                if (!in_array($file->getFileName(), $allowedPubFiles)) {
                    $externalFiles[] = $file->getFileName();
                }
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
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Unauthorized custom files');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckExternalPHPFilesInPubFolder';
    }

    /**
     * @return int
     * @throws FileSystemException
     */
    public function getType(): int
    {
        return (int)$this->issueExists();
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return (int)$this->position;
    }

    /**
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
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__('Identify suspicious or unknown files that may indicate a security breach.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
