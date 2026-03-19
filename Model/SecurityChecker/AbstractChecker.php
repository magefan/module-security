<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;

abstract class AbstractChecker implements SecurityCheckerInterface
{
    public const RESOLVED_MESSAGE = 'Resolved.';

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var bool
     */
    protected $cacheLoaded = false;

    /**
     * @var int
     */
    protected $issueExists = SecurityCheckerInterface::CANT_CHECK;

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     */
    public function __construct(
        SecurityStatusCacheFactory $securityStatusCacheFactory
    ) {
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
    }

    /**
     * Load cache
     *
     * @return $this
     */
    public function loadCache()
    {
        if (!$this->cacheLoaded) {
            $this->cacheLoaded = true;
            $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
            if ($securityStatus->getId()) {
                $this->issueExists = $securityStatus->getIssueExists();
                $this->details = $securityStatus->getDetails();
            }
        }

        return $this;
    }

    /**
     * Update cache
     *
     * @return mixed
     */
    abstract public function updateCache();

    /**
     * Get status code
     *
     * @return mixed
     */
    abstract public function getCode(): string;

    /**
     * Get name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get type
     *
     * @return int
     */
    public function getType(): int
    {
        return self::CRITICAL;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return 1;
    }

    /**
     * Get total records
     *
     * @return int
     */
    public function getTotal(): int
    {
        return 1;
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getDetails(): array
    {
        return [];
    }

    /**
     * Get suggestions
     *
     * @return string
     */
    public function getSuggestions(): string
    {
        return '';
    }

    /**
     * Get resolved text
     *
     * @return string
     */
    public function getResolvedMessage(): string
    {
        return (string)__('Resolved.');
    }
}
