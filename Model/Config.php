<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Magefan Security SystemConfig Model
 */
class Config
{
    /**
     * Extension enabled config path
     */
    public const XML_PATH_EXTENSION_ENABLED = 'mfsecurity/general/enabled';

    /**
     * Admin email notification enabled config path
     */
    public const XML_PATH_NOTIFY_ADMIN_EMAIL_ENABLED = 'mfsecurity/email_notification/enabled';

    /**
     * Admin notification email sender identity config path
     */
    public const XML_PATH_NOTIFY_ADMIN_EMAIL_SENDER = 'mfsecurity/email_notification/sender';

    /**
     * Admin notification email template config path
     */
    public const XML_PATH_NOTIFY_ADMIN_EMAIL_TEMPLATE = 'mfsecurity/email_notification/template';

    /**
     * Admin notification email recipients config path
     */
    public const XML_PATH_NOTIFY_ADMIN_EMAIL_RECIPIENTS = 'mfsecurity/email_notification/recipients';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SystemConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if enabled
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Check if admin email notification is enabled
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isEmailNotificationEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_PATH_NOTIFY_ADMIN_EMAIL_ENABLED,
            $storeId
        );
    }

    /**
     * Get notification email sender identity (e.g. 'general', 'sales')
     *
     * @param mixed $storeId
     * @return string
     */
    public function getNotificationEmailSender($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_NOTIFY_ADMIN_EMAIL_SENDER,
            $storeId
        );
    }

    /**
     * Get notification email template identifier
     *
     * @param mixed $storeId
     * @return string
     */
    public function getNotificationEmailTemplate($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_NOTIFY_ADMIN_EMAIL_TEMPLATE,
            $storeId
        );
    }

    /**
     * Get notification email recipients (comma-separated)
     *
     * @param mixed $storeId
     * @return string
     */
    public function getNotificationEmailRecipients($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_NOTIFY_ADMIN_EMAIL_RECIPIENTS,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     *
     * @param string $path
     * @param mixed $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
