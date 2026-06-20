<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Controller\Adminhtml\Security;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Status extends Action
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param CacheInterface $cache
     * @param Json $json
     * @param Context $context
     */
    public function __construct(
        CacheInterface $cache,
        Json           $json,
        Context        $context
    ) {
        $this->cache = $cache;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * Return JSON progress for the given token.
     */
    public function execute()
    {
        $token = (string)$this->getRequest()->getParam('token', '');
        $data  = $token ? $this->cache->load('mfsecurity_progress_' . $token) : null;

        if ($data) {
            $progress = $this->json->unserialize($data);
        } else {
            $progress = ['done' => 0, 'total' => 1];
        }

        $total    = max(1, (int)($progress['total'] ?? 1));
        $done     = (int)($progress['done'] ?? 0);
        $percent  = (int)round($done / $total * 100);
        $complete = $done >= $total;

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'done'     => $done,
            'total'    => $total,
            'percent'  => $percent,
            'complete' => $complete
        ]);
        return $result;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_Security::refresh');
    }
}
