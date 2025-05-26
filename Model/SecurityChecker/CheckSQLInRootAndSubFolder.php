<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

class CheckSQLInRootAndSubFolder extends AbstractChecker
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
     * @var Json
     */
    private $json;

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var string[]
     */
    private $exclude = [
        'vendor/laminas/laminas-db/',
        'vendor/magento/magento2-base/dev/',
        'dev/tests/'
    ];

    /**
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Json $json
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param $position
     */
    public function __construct(
        DirectoryList              $directoryList,
        File                       $file,
        Json                       $json,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        $position = null
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->json = $json;
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
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
     * @return CheckSQLInRootAndSubFolder
     * @throws Exception
     */
    public function updateCache()
    {
        $rootFolder = $this->directoryList->getRoot();
        //$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootFolder, \FilesystemIterator::SKIP_DOTS));
        $directoryIterator = new \RecursiveDirectoryIterator(
            $rootFolder,
            \FilesystemIterator::SKIP_DOTS
        );

        $filterIterator = new \RecursiveCallbackFilterIterator($directoryIterator, [$this, 'filterCallback']);

        $iterator = new \RecursiveIteratorIterator($filterIterator);
        
        $sqlPathFiles = [];

        foreach ($iterator as $file) {
            $fileInfo = $this->file->getPathInfo($file->getPathName());
            if ($file->isFile() && isset($fileInfo['extension']) && $fileInfo['extension'] == 'sql') {
                if (!$this->isExcluded($file->getPathname())) {
                    $sqlPathFiles[] = $file->getPathname();
                }
            }
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                count($sqlPathFiles) ?
                    SecurityCheckerInterface::CRITICAL :
                    SecurityCheckerInterface::OK
            )
            ->setDetails($this->json->serialize($sqlPathFiles))
            ->save();

        $this->cacheLoaded = false;

        return $this;
    }

        /**
     * @param mixed $current
     * @param mixed $key
     * @param mixed $iterator
     * @return bool
     */
    private function filterCallback($current, $key, $iterator): bool
    {
        if ($current->isLink()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isExcluded(string $path): bool
    {
        $result = false;

        foreach ($this->exclude as $excludePath) {
            if (strpos($path, $this->directoryList->getRoot() . '/' . $excludePath) === 0) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Exposed SQL dump files');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckSQLInRootAndSubFolder';
    }

    /**
     * @return int
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
            ? (string)__('Prevent database leaks by ensuring SQL backups are not publicly accessible. Remove SQL files from public directories.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
