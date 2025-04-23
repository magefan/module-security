<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Controller\Adminhtml\Security;

use Magefan\Security\Model\Config;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Dashboard extends \Magento\Backend\App\Action
{
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
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Config      $config,
        Context     $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Security Dashboard by Magefan"));

        if (!$this->config->isEnabled()) {
            $this->messageManager->addWarningMessage(__('Mage' . 'fan Sec' . 'ur' . 'i' . 'ty' . ' is dis' . 'abled. Plea'
                . 'se enable it in Stores > Configuration > Mag' . 'efan Extensions > Sec' . 'ur' . 'i' . 'ty.'));

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultPage->setUrl($this->_redirect->getRedirectUrl('adminhtml/system_config/edit/section/security'));
        }


        return $resultPage;
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Magefan_Security::dashboardpage");
    }
}
