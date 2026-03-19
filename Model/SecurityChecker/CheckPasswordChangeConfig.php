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

class CheckPasswordChangeConfig extends AbstractChecker
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
     * @param mixed $position
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
     * Check is issue exist
     *
     * @return int
     */
    public function issueExists()
    {
        if (null === $this->issueExists || $this->issueExists === SecurityCheckerInterface::CANT_CHECK) {
            $isIssueExist = (bool)!($this->scopeConfig->getValue(self::XML_ADMIN_SECURITY_PASSWORD_IS_FORCED));
            $this->issueExists = $isIssueExist
                ? SecurityCheckerInterface::NOTICE
                : SecurityCheckerInterface::OK;
        }

        return $this->issueExists;
    }

    /**
     * Update cache
     *
     * @return CheckPasswordChangeConfig
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
        return (string)__('Weak password change policy');
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckPasswordChangeConfig';
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
     * Get suggestions
     *
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__(
                'Require forced password changes. Set the "Password Change" option to "Forced" '.
                ' in Stores > Configuration > Advanced > Admin > Security section. %1',
                '<a target="_blank" href="' .
                $this->url->getUrl('adminhtml/system_config/edit/section/admin') .
                '">' .
                    __('Change').
                '</a>.'
            )
            : $this->getResolvedMessage();
    }
}
