<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Cron;

use Magefan\Security\Api\SecurityCheckerInterface;
use Magefan\Security\Api\SecurityCheckerPoolInterface;
use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Model\Config;
use Magefan\Security\Model\Notification\EmailSender;
use Exception;

class UpdateStatus
{
    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SecurityCheckerPoolInterface
     */
    private $securityCheckerPool;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * Constructor
     *
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     * @param Config $config
     * @param SecurityCheckerPoolInterface $securityCheckerPool
     * @param EmailSender $emailSender
     */
    public function __construct(
        SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache,
        Config                              $config,
        SecurityCheckerPoolInterface        $securityCheckerPool,
        EmailSender                         $emailSender
    ) {
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
        $this->config = $config;
        $this->securityCheckerPool = $securityCheckerPool;
        $this->emailSender = $emailSender;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $notifyEnabled = $this->config->isEmailNotificationEnabled();

        // Snapshot state of ALL checkers (DB-backed and live) before the update
        $previousState = [];
        if ($notifyEnabled) {
            foreach ($this->securityCheckerPool->get() as $checker) {
                $previousState[$checker->getCode()] = [
                    'issue_exists' => (int)$checker->issueExists(),
                    'details'      => $checker->getDetails(),
                ];
            }
        }

        $this->securityCheckerUpdateCache->execute();

        if (!$notifyEnabled) {
            return;
        }

        // Fresh pool instances so DB-backed checkers reload updated values
        $newIssues = $this->detectNewIssues($previousState);

        if (!empty($newIssues)) {
            $this->emailSender->send($newIssues);
        }
    }

    /**
     * Compare fresh checker results against the pre-update snapshot.
     * Returns names of checkers that transitioned from non-issue to issue,
     * or whose detail items grew (new findings within an already-flagged check).
     *
     * @param array $previousState [code => ['issue_exists' => int, 'details' => array]]
     * @return string[]
     */
    private function detectNewIssues(array $previousState): array
    {
        $newIssueNames = [];

        foreach ($this->securityCheckerPool->get() as $checker) {
            $currentStatus = (int)$checker->issueExists();

            $isIssue = in_array(
                $currentStatus,
                [SecurityCheckerInterface::CRITICAL, SecurityCheckerInterface::NOTICE],
                true
            );
            if (!$isIssue) {
                continue;
            }

            $previous        = $previousState[$checker->getCode()] ?? [];
            $previousStatus  = $previous['issue_exists'] ?? SecurityCheckerInterface::CANT_CHECK;
            $previousDetails = $previous['details'] ?? [];

            $wasIssue = in_array(
                $previousStatus,
                [SecurityCheckerInterface::CRITICAL, SecurityCheckerInterface::NOTICE],
                true
            );

            if (!$wasIssue) {
                $newIssueNames[] = $checker->getName();
            } elseif ($this->hasNewDetailItems($checker->getDetails(), $previousDetails)) {
                $newIssueNames[] = $checker->getName();
            }
        }

        return $newIssueNames;
    }

    /**
     * Returns true when $current contains items not present in $previous.
     * Handles both flat indexed arrays (list of paths/messages) and
     * associative nested structures (keyed by file path).
     *
     * @param array $current
     * @param array $previous
     * @return bool
     */
    private function hasNewDetailItems(array $current, array $previous): bool
    {
        if (empty($current)) {
            return false;
        }

        // Flat indexed array: ["file1", "file2", ...]
        if (array_keys($current) === range(0, count($current) - 1)) {
            return !empty(array_diff($current, $previous));
        }

        // Associative / nested structure: compare by top-level keys (file paths)
        return !empty(array_diff_key($current, $previous));
    }
}
