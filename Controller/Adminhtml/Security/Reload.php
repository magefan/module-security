<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Controller\Adminhtml\Security;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Api\SecurityCheckerPoolInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magefan\Security\Model\Config;
use Magento\Framework\Serialize\Serializer\Json;

class Reload extends \Magento\Backend\App\Action
{
    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @var SecurityCheckerPoolInterface
     */
    private $securityCheckerPool;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     * @param SecurityCheckerPoolInterface $securityCheckerPool
     * @param CacheInterface $cache
     * @param Json $json
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache,
        SecurityCheckerPoolInterface        $securityCheckerPool,
        CacheInterface                      $cache,
        Json                                $json,
        PageFactory                         $resultPageFactory,
        Config                              $config,
        Context                             $context
    ) {
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
        $this->securityCheckerPool = $securityCheckerPool;
        $this->cache = $cache;
        $this->json = $json;
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Update security data
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->isAjax();

        if (!$this->config->isEnabled()) {
            if ($isAjax) {
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $result->setData(['error' => (string)__('Magefan Security is disabled.')]);
                return $result;
            }
            $this->messageManager->addWarningMessage(
                __('Mage' . 'fan Sec' . 'ur' . 'i' . 'ty' . ' is dis' . 'abled. Plea' .
                    'se enable it in Stores> Configuration > Mag' . 'efan Extensions > Sec' . 'ur' . 'i' . 'ty.')
            );
        } else {
            $code  = $this->_request->getParam('code', null);
            $token = bin2hex(random_bytes(8));

            $total = $code ? 1 : count($this->securityCheckerPool->get());
            $this->cache->save(
                $this->json->serialize(['done' => 0, 'total' => $total]),
                'mfsecurity_progress_' . $token,
                [],
                3600
            );

            $this->dispatchBackgroundRefresh($code, $token);

            if ($isAjax) {
                $statusUrl = $this->getUrl('mfsecurity/security/status', [
                    'token' => $token,
                    '_nosid' => true
                ]);
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $result->setData(['token' => $token, 'total' => $total, 'status_url' => $statusUrl]);
                return $result;
            }

            $this->messageManager->addSuccessMessage(__('Security status refresh has been queued.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Run the security refresh as a background CLI process so the HTTP
     * response returns immediately (avoids gateway timeouts on Cloudflare/nginx).
     * Falls back to synchronous execution when exec() is not available.
     *
     * @param string|null $code
     * @param string|null $token
     * @return void
     */
    private function dispatchBackgroundRefresh($code, $token)
    {
        if (!function_exists('exec')) {
            try {
                $this->securityCheckerUpdateCache->execute($code, $token);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Security status update failed: %1', $e->getMessage())
                );
            }
            return;
        }

        $phpBin = PHP_BINARY;
        if (empty($phpBin) || false !== stripos(basename($phpBin), 'fpm')) {
            $phpBin = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
            if (!is_executable($phpBin)) {
                $phpBin = 'php';
            }
        }
        $php  = escapeshellarg($phpBin);
        $mage = escapeshellarg(BP . '/bin/magento');
        $cmd  = $php . ' ' . $mage . ' mfsecurity:refresh';

        if ($code) {
            $cmd .= ' --code=' . escapeshellarg($code);
        }
        if ($token) {
            $cmd .= ' --token=' . escapeshellarg($token);
        }

        exec($cmd . ' > /dev/null 2>&1 &');
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
