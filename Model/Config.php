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
    const XML_PATH_EXTENSION_ENABLED = 'mfsecurity/general/enabled';

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
     * @param $storeId
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
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
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
