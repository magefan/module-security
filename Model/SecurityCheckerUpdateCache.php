<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Api\SecurityCheckerPoolInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Exception;

class SecurityCheckerUpdateCache implements SecurityCheckerUpdateCacheInterface
{

    /**
     * @var Json
     */
    private $json;

    /**
     * @var SecurityCheckerPoolInterface
     */
    private $securityCheckerPool;

    /**
     * @param Json $json
     * @param SecurityCheckerPoolInterface $securityCheckerPool
     */
    public function __construct(
        Json                         $json,
        SecurityCheckerPoolInterface $securityCheckerPool,
    ) {
        $this->json = $json;
        $this->securityCheckerPool = $securityCheckerPool;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function execute($code = null)
    {
        foreach ($this->securityCheckerPool->get() as $securityChecker) {
            if ($code && $securityChecker->getCode() != $code) {
                continue;
            }

            $securityChecker->updateCache();
        }
    }
}
