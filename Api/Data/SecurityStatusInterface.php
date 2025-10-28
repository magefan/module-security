<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Api\Data;

use Magefan\Security\Model\SecurityStatusCache;

interface SecurityStatusInterface
{

    public const CODE = 'code';
    public const SECURITYSTATUS_ID = 'id';

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $securityStatusId
     * @return SecurityStatusCache
     */
    public function setId($securityStatusId): SecurityStatusCache;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     * @return SecurityStatusCache
     */
    public function setCode(string $code): SecurityStatusCache;

    /**
     * Check if issue still exist
     *
     * @return int
     */
    public function getIssueExists(): int;

    /**
     * Set issue exist
     *
     * @param int $issueExists
     * @return SecurityStatusCache
     */
    public function setIssueExists(int $issueExists): SecurityStatusCache;

    /**
     * Get deteils
     *
     * @return string
     */
    public function getDetails(): string;

    /**
     * Set details
     *
     * @param string $details
     * @return SecurityStatusCache
     */
    public function setDetails(string $details): SecurityStatusCache;
}
