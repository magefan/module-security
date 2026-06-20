<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Api\SecurityCheckerPoolInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param Json $json
     * @param SecurityCheckerPoolInterface $securityCheckerPool
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     */
    public function __construct(
        Json                         $json,
        SecurityCheckerPoolInterface $securityCheckerPool,
        LoggerInterface              $logger,
        CacheInterface               $cache
    ) {
        $this->json = $json;
        $this->securityCheckerPool = $securityCheckerPool;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Update cache, writing per-checker progress under $token when supplied.
     *
     * @param string|null $code   Refresh only this checker code (null = all).
     * @param string|null $token  Progress-tracking token written to cache.
     * @return void
     */
    public function execute($code = null, $token = null)
    {
        $done = 0;

        foreach ($this->securityCheckerPool->get() as $securityChecker) {
            if ($code && $securityChecker->getCode() != $code) {
                continue;
            }

            try {
                $securityChecker->updateCache();
            } catch (Exception $e) {
                $this->logger->error(
                    'Security checker "' . $securityChecker->getCode() . '" failed: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }

            $done++;

            if ($token) {
                $this->saveProgress($token, $done);
            }
        }
    }

    /**
     * Persist the number of completed checkers for the given token.
     *
     * @param string $token
     * @param int    $done
     * @return void
     */
    private function saveProgress($token, $done)
    {
        $key      = 'mfsecurity_progress_' . $token;
        $existing = $this->cache->load($key);
        $data     = $existing ? $this->json->unserialize($existing) : ['total' => 1];
        $data['done'] = $done;
        $this->cache->save($this->json->serialize($data), $key, [], 3600);
    }
}
