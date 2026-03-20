<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model;

use Magefan\Security\Api\Data\SecurityStatusInterface;
use Magento\Framework\Model\AbstractModel;

class SecurityStatusCache extends AbstractModel implements SecurityStatusInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magefan\Security\Model\ResourceModel\SecurityStatusCache::class);
    }

    /**
     * Get id
     *
     * @return array|mixed|null
     */
    public function getId()
    {
        return $this->getData(self::SECURITYSTATUS_ID);
    }

    /**
     * Set id
     *
     * @param int $id
     * @return SecurityStatusCache
     */
    public function setId($id): SecurityStatusCache
    {
        return $this->setData(self::SECURITYSTATUS_ID, $id);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->getData(self::CODE);
    }

    /**
     * Set code
     *
     * @param string $code
     * @return SecurityStatusCache
     */
    public function setCode(string $code): SecurityStatusCache
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Check if issue exist
     *
     * @return int
     */
    public function getIssueExists(): int
    {
        return (int)$this->getData('issue_exists');
    }

    /**
     * Set if issue exist
     *
     * @param int $issueExists
     * @return SecurityStatusCache
     */
    public function setIssueExists(int $issueExists): SecurityStatusCache
    {
        return $this->setData('issue_exists', $issueExists);
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails(): string
    {
        return (string)$this->getData('details');
    }

    /**
     * Set details
     *
     * @param string $details
     * @return SecurityStatusCache
     */
    public function setDetails(string $details): SecurityStatusCache
    {
        return $this->setData('details', $details);
    }
}
