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

class CheckArchiveInRootAndSubFolder extends AbstractChecker
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
     * @var string[]
     */
    private $exclude = [
        'var/composer_home/',
        'vendor/magento/magento2-base/dev/',
        'dev/tests/',
        'update/vendor/composer'
    ];

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
     * @return $this
     * @throws Exception
     */
    public function updateCache()
    {
        $rootFolder = $this->directoryList->getRoot();
        $archiveExtensions = ['zip', 'tar', 'gz', 'tgz', 'tar.gz'];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootFolder, \FilesystemIterator::SKIP_DOTS));
        $archives = [];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileInfo = $this->file->getPathInfo($file->getPathName());
                if (isset($fileInfo['extension']) && in_array($fileInfo['extension'], $archiveExtensions)) {
                    if (!$this->isExcluded($file->getPathname())) {
                        $archives[] = $file->getPathname();
                    }
                }
            }
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                count($archives) ?
                    SecurityCheckerInterface::NOTICE :
                    SecurityCheckerInterface::OK
            )
            ->setDetails($this->json->serialize($archives))
            ->save();

        $this->cacheLoaded = false;

        return $this;
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
        return (string)__('Backup files in Magento directory');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckArchiveInRootAndSubFolder';
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
            ? (string)__('Remove sensitive backup files that attackers can access. Move them to another location.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
