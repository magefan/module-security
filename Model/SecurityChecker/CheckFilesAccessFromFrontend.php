<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magento\Framework\Exception\NoSuchEntityException;
use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;

class CheckFilesAccessFromFrontend extends AbstractChecker
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Curl
     */
    private $curl;

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
     * @param StoreManagerInterface $storeManager
     * @param Curl $curl
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param Json $json
     * @param $position
     */
    public function __construct(
        StoreManagerInterface      $storeManager,
        Curl                       $curl,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        Json                       $json,
        $position = null
    ) {
        $this->storeManager = $storeManager;
        $this->curl = $curl;
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
     * @throws NoSuchEntityException
     */
    public function updateCache()
    {
        $filesToCheck = [
            'app/',
            'bin/',
            'dev/',
            'generated/',
            'lib/',
            'phpserver/',
            'pub',
            'setup/',
            'var/',
            'vendor/',
            'app/etc/env.php',
            'var/log/system.log',
            'var/log/exception.log',
            '.htaccess',
            'composer.json',
            'composer.lock',
            'app/etc/config.php',
            'pub/errors/',
            'pub/media/.htaccess',
            'bin/magento',
        ];

        $webUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $accessibleFoldersAndFiles = [];
        foreach ($filesToCheck as $file) {
            $fileUrl = $webUrl . $file;

            try {
                $this->curl->get($fileUrl);
                $statusCode = $this->curl->getStatus();

                if ($statusCode === 200) {
                    $accessibleFoldersAndFiles[] = (string)__('File or directory <strong>%1</strong> is accessible: %2', $file, $fileUrl);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                count($accessibleFoldersAndFiles) ?
                    SecurityCheckerInterface::CRITICAL :
                    SecurityCheckerInterface::OK
            )
            ->setDetails($this->json->serialize($accessibleFoldersAndFiles))
            ->save();

        $this->cacheLoaded = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Unrestricted file access');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckFilesAccessFromFrontend';
    }

    /**
     * @return int
     * @throws NoSuchEntityException
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
            ? (string)__('Block direct access to sensitive files from the frontend.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
