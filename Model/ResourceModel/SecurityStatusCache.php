<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SecurityStatusCache extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('magefan_security_status_cache', 'id');
    }
}
