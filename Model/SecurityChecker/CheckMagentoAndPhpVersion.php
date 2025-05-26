<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Exception;
use Magento\Framework\Serialize\Serializer\Json;

class CheckMagentoAndPhpVersion extends AbstractChecker
{

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var mixed|null
     */
    private $position;

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var
     */
    protected $details = [];

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param Curl $curl
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param $position
     */
    public function __construct(
        ProductMetadataInterface   $productMetadata,
        Curl                       $curl,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        Json                       $json,
        $position = null
    ) {
        $this->productMetadata = $productMetadata;
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
     * @throws Exception
     */
    public function updateCache()
    {
        $details = [];
        $currentMagentoVersion = $this->productMetadata->getVersion();
        $latestMagentoVersion = 'none';

        try {
            $this->curl->addHeader('User-Agent', 'PHP');
            $this->curl->get("https://api.github.com/repos/magento/magento2/tags");
            $response = $this->curl->getBody();
            if ($response) {
                $tags = json_decode($response, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    foreach ($tags as $key => $tag) {
                        if (!isset($tag['name']) || (false !== strpos($tag['name'], 'beta'))) {
                            continue;
                        }

                        if (!$latestMagentoVersion) {
                            $latestMagentoVersion = $tag['name'];
                            continue;
                        }

                        if (version_compare($tag['name'], $latestMagentoVersion, '>')) {
                            $latestMagentoVersion = $tag['name'];
                            continue;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $latestMagentoVersion = 'none';
        }

        $isIssueExist = (bool)version_compare($currentMagentoVersion, $latestMagentoVersion, '<');
        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');

        if ($isIssueExist) {
            $details[] =
                (string)__(
                    'Please update Magento to the latest version %1, you use %2 <a target="_blank" href="https://magefan.com/blog/update-magento-2">%3</a>.',
                    $latestMagentoVersion,
                    $currentMagentoVersion,
                    __('Update')
                );
        }

        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                $isIssueExist ?
                    SecurityCheckerInterface::NOTICE :
                    SecurityCheckerInterface::OK
            )
            ->setDetails(
                $this->json->serialize($details)
            )->save();

        $this->cacheLoaded = false;

        return $this;
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
    public function getName(): string
    {
        return (string)__('Outdated Magento/PHP version');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckMagentoAndPhpVersion';
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
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__('Ensure all latest security patches and performance improvements are applied.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
