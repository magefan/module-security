<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;
use Exception;

class CheckTwoFactorAuthentication extends AbstractChecker
{
    const TWO_FACTOR_MODULE_NAME = 'Magento_TwoFactorAuth';
    const TWO_FACTOR_IMS_MODULE_NAME = 'Magento_AdminAdobeImsTwoFactorAuth';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

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
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleManager $moduleManager
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param $position
     */
    public function __construct(
        ScopeConfigInterface       $scopeConfig,
        ModuleManager              $moduleManager,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        $position = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
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
            if (!$this->moduleManager->isEnabled(self::TWO_FACTOR_MODULE_NAME) ||
                !$this->moduleManager->isEnabled(self::TWO_FACTOR_IMS_MODULE_NAME)) {
                $this->issueExists = SecurityCheckerInterface::NOTICE;
            } else {
                $this->issueExists = SecurityCheckerInterface::OK;
            }
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
        return (string)__('Two-factor authentication disabled');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckTwoFactorAuthentication';
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
            ? (string)__('Add an extra security layer for admin logins. Please enable the"Magento_TwoFactorAuth" and "Magento_AdminAdobeImsTwoFactorAuth" in config.php.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
