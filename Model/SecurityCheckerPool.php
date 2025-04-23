<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magefan\Security\Api\SecurityCheckerPoolInterface;

class SecurityCheckerPool implements SecurityCheckerPoolInterface
{
    /**
     * @var array
     */
    private $actionPool;

    /**
     * @param array $actionPool
     */
    public function __construct(
        array $actionPool
    ) {
        $this->actionPool = $actionPool;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $pool = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($this->actionPool as $detailArray) {
            $pool[] = $objectManager->create($detailArray['class'], ['position' => $detailArray['position']]);
        }

        return $pool;
    }
}
