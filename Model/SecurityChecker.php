<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magefan\Security\Api\SecurityCheckerListInterface;
use Magefan\Security\Api\SecurityCheckerPoolInterface;
use Magento\Framework\Serialize\Serializer\Json;

class SecurityChecker implements SecurityCheckerListInterface
{

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var SecurityCheckerPoolInterface
     */
    private $securityCheckerPool;

    /**
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param Json $json
     * @param SecurityCheckerPoolInterface $securityCheckerPool
     */
    public function __construct(
        SecurityStatusCacheFactory   $securityStatusCacheFactory,
        Json                         $json,
        SecurityCheckerPoolInterface $securityCheckerPool
    ) {
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->json = $json;
        $this->securityCheckerPool = $securityCheckerPool;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $securityData = [];
        foreach ($this->securityCheckerPool->get() as $securityChecker) {
            $securityData[] = $securityChecker;
        }

        return $securityData;
    }
}
