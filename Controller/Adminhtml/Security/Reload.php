<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Controller\Adminhtml\Security;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magefan\Security\Model\Config;
use Exception;

class Reload extends \Magento\Backend\App\Action
{
    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache,
        PageFactory                $resultPageFactory,
        Config                     $config,
        Context                    $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
        parent::__construct($context);
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            $this->messageManager->addWarningMessage(__('Mage' . 'fan Sec' . 'ur' . 'i' . 'ty' . ' is dis' . 'abled. Plea'
                . 'se enable it in Stores > Configuration > Mag' . 'efan Extensions > Sec' . 'ur' . 'i' . 'ty.'));
        } else {
            $code = $this->_request->getParam('code', null);
            $this->securityCheckerUpdateCache->execute($code);
            $this->messageManager->addSuccessMessage(__('Security status successfully updated'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Magefan_Security::refresh");
    }
}
