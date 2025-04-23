<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Framework\App\AreaList;
use Exception;

class CheckAdminFrontNameConfig extends AbstractChecker
{

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var mixed|null
     */
    private $position;

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var int
     */
    protected $issueExists = SecurityCheckerInterface::CANT_CHECK;

    /**
     * @param AreaList $areaList
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param $position
     */
    public function __construct(
        AreaList                   $areaList,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        $position = null
    ) {
        $this->areaList = $areaList;
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->position = $position;
        parent::__construct($securityStatusCacheFactory);
    }

    /**
     * @return int
     */
    public function issueExists()
    {
        if (null === $this->issueExists || $this->issueExists === SecurityCheckerInterface::CANT_CHECK) {
            $this->issueExists = $this->areaList->getFrontName('adminhtml') == 'admin' ?
                SecurityCheckerInterface::CRITICAL :
                SecurityCheckerInterface::OK;
        }

        return $this->issueExists;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function updateCache()
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Insecure default admin panel path');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckAdminFrontNameConfig';
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
            ? (string)__('Reduce brute-force attacks by changing the default "admin" path in app/etc/env.php file > change ‘frontName’ value. %1.', '<a target="_blank" href="https://magefan.com/blog/magento-change-admin-url">' .__('Reed more'). '</a>')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
