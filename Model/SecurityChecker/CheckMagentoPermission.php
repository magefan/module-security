<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magento\Framework\Exception\FileSystemException;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\DirectoryList;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Magento\Framework\Shell;

class CheckMagentoPermission extends AbstractChecker
{

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FileDriver
     */
    private $fileDriver;

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
     * @var Shell
     */
    private $shell;

    /**
     * @param DirectoryList $directoryList
     * @param FileDriver $fileDriver
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param Json $json
     * @param Shell $shell
     * @param $position
     */
    public function __construct(
        DirectoryList $directoryList,
        FileDriver $fileDriver,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        Json $json,
        Shell $shell,
        $position = null
    ) {
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->json = $json;
        $this->shell = $shell;
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
     * @throws FileSystemException
     */
    public function updateCache()
    {
        $magentoDir = $this->directoryList->getRoot();
        $permissionErrors = [];

        /*
        -perm -0002 | Finds files/directories that anyone can write to (critical risk!).
        -perm 0777  | Finds fully open files (worst-case scenario).
        -perm -111  | Finds .php files with execute (+x) permission, which could indicate a security misconfiguration or webshell risk.
        */
        $findFiles = "find $magentoDir -type f \\( -perm -0002 -o -perm 0777 \\)";
        $findDirs = "find $magentoDir -type d -perm -0002";
        $findExecPhp = "find $magentoDir -type f -name '*.php' -perm -111";

        // Execute the commands
        $filesOutput = $this->shell->execute($findFiles);
        $dirsOutput = $this->shell->execute($findDirs);
        $execPhpOutput = $this->shell->execute($findExecPhp);

        // Output results
        if ($filesOutput) {
            $filesOutput = explode(PHP_EOL, $filesOutput);
            foreach ($filesOutput as $item) {
                $permissionErrors[] = (string)__('File with incorrect permissions: %1 (recommend 06444)', $item);
            }
        }

        if ($dirsOutput) {
            $dirsOutput = explode(PHP_EOL, $dirsOutput);
            foreach ($dirsOutput as $item) {
                $permissionErrors[] = (string)__('Directory with incorrect permissions: %1 (recommend 0755)', $item);
            }
        }

        if ($execPhpOutput) {
            $execPhpOutput = explode(PHP_EOL, $execPhpOutput);
            foreach ($execPhpOutput as $item) {
                if (strpos($item, $magentoDir . '/generated/code') !== false) {
                    continue;
                }

                $permissionErrors[] = (string)__('Executable PHP file: %1 (should not have +x)', $item);
            }
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                count($permissionErrors)
                    ? SecurityCheckerInterface::NOTICE
                    : SecurityCheckerInterface::OK
            )
            ->setDetails($this->json->serialize($permissionErrors))
            ->save();

        $this->cacheLoaded = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Insecure file and folder permissions');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckMagentoPermission';
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
            ? (string)__('Prevent unauthorized modifications and access to sensitive files. %1.', '<a target="_blank" href="https://magefan.com/blog/magento-file-system-permissions">' .__('Reed more'). '</a>')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
