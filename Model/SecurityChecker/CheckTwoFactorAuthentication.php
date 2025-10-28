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
    public const TWO_FACTOR_MODULE_NAME = 'Magento_TwoFactorAuth';
    public const TWO_FACTOR_IMS_MODULE_NAME = 'Magento_AdminAdobeImsTwoFactorAuth';

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
     * @param mixed $position
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
     * Check if issue exist
     *
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
     * Update cache
     *
     * @return CheckTwoFactorAuthentication
     * @throws Exception
     */
    public function updateCache()
    {
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Two-factor authentication disabled');
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckTwoFactorAuthentication';
    }

    /**
     * Get type
     *
     * @return int
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
     * Get suggestion
     *
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__('Add an extra security layer for admin logins. Please enable ' .
                ' the"Magento_TwoFactorAuth" and "Magento_AdminAdobeImsTwoFactorAuth" in config.php.')
            : $this->getResolvedMessage();
    }
}
