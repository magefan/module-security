<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Backend\Model\UrlInterface;
use Exception;

class CheckRecaptcha extends AbstractChecker
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var mixed|null
     */
    private $position;

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var int
     */
    protected $issueExists = SecurityCheckerInterface::CANT_CHECK;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param UrlInterface $url
     * @param null $position
     */
    public function __construct(
        ScopeConfigInterface       $scopeConfig,
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        UrlInterface             $url,
        $position = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->url = $url;
        $this->position = $position;
        parent::__construct($securityStatusCacheFactory);
    }

    /**
     * @return int
     */
    public function issueExists()
    {
        if (null === $this->issueExists || $this->issueExists === SecurityCheckerInterface::CANT_CHECK) {
            $isIssueExist = !(bool)$this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_ADMIN_LOGIN) || !(bool)$this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_RESET_PASSWORD);
            $this->issueExists = $isIssueExist ?
                SecurityCheckerInterface::NOTICE :
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
        return  (string)__('Missing reCAPTCHA for admin login and reset password');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckRecaptcha';
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->issueExists();
    }

    /**
     * @return mixed|null
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
            ? (string)__('Prevent bot attacks on login and password reset pages. Enable the "Enable for Login" and "Enable for Forgot Password" option in Stores > Configuration > Security > Google reCAPTCHA Admin Panel > Admin Panel. %1', '<a target="_blank" href="' . $this->url->getUrl('adminhtml/system_config/edit/section/recaptcha_backend') . '">' .__('Change'). '</a>.')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
