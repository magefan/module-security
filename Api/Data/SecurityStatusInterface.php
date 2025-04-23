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

    const CODE = 'code';
    const SECURITYSTATUS_ID = 'id';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $securityStatusId
     * @return SecurityStatusCache
     */
    public function setId($securityStatusId): SecurityStatusCache;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     * @return SecurityStatusCache
     */
    public function setCode(string $code): SecurityStatusCache;

    /**
     * @return int
     */
    public function getIssueExists(): int;

    /**
     * @param $issueExists
     * @return SecurityStatusCache
     */
    public function setIssueExists(int $issueExists): SecurityStatusCache;

    /**
     * @return string
     */
    public function getDetails(): string;

    /**
     * @param string $details
     * @return SecurityStatusCache
     */
    public function setDetails(string $details): SecurityStatusCache;
}
