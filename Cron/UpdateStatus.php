<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Cron;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Model\Config;
use Exception;

class UpdateStatus
{
    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     * @param Config $config
     */
    public function __construct(
        SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache,
        Config                     $config
    ) {
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
        $this->config = $config;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        if ($this->config->isEnabled()) {
            $this->securityCheckerUpdateCache->execute();
        }
    }
}
