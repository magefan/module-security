<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\ResourceModel\SecurityStatusCache;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(
            \Magefan\Security\Model\SecurityStatusCache::class,
            \Magefan\Security\Model\ResourceModel\SecurityStatusCache::class
        );
    }
}
