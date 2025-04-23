<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\SecurityChecker;

use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magefan\Security\Model\SecurityStatusCacheFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magefan\Security\Api\SecurityCheckerInterface;
use Magento\Backend\Model\UrlInterface;
use Exception;

class CheckUsersPermission extends AbstractChecker
{

    /**
     * @var mixed|null
     */
    private $position;

    /**
     * @var
     */
    protected $details = [];

    /**
     * @var SecurityStatusCacheFactory
     */
    private $securityStatusCacheFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param SecurityStatusCacheFactory $securityStatusCacheFactory
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $url
     * @param null $position
     */
    public function __construct(
        SecurityStatusCacheFactory $securityStatusCacheFactory,
        CollectionFactory          $collectionFactory,
        UrlInterface               $url,
        $position = null
    ) {
        $this->securityStatusCacheFactory = $securityStatusCacheFactory;
        $this->collectionFactory = $collectionFactory;
        $this->url = $url;
        $this->position = $position;
        parent::__construct($securityStatusCacheFactory);
    }

    /**
     * @return int
     */
    public function issueExists()
    {
        $this->loadCache();
        return $this->issueExists;
    }

    /**
     * @return $this
     * @throws NoSuchEntityException|Exception
     */
    public function updateCache()
    {
        $isAllAdmin = true;
        $userCollection = $this->collectionFactory->create();

        if (count($userCollection) > 1) {
            foreach ($userCollection as $user) {
                if ($user->getRoleName() != 'Administrators') {
                    $isAllAdmin = false;
                    break;
                }
            }
        } else {
            $isAllAdmin = false;
        }

        $securityStatus = $this->securityStatusCacheFactory->create()->load($this->getCode(), 'code');
        $securityStatus
            ->setCode($this->getCode())
            ->setIssueExists(
                $isAllAdmin ?
                    SecurityCheckerInterface::NOTICE :
                    SecurityCheckerInterface::OK
            )
            ->save();

        $this->cacheLoaded = false;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)__('Excessive admin permissions');
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'CheckUsersPermission';
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->issueExists();
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return (int)$this->position;
    }

    /**
     * @return string
     */
    public function getSuggestions(): string
    {
        return $this->issueExists != SecurityCheckerInterface::OK
            ? (string)__('Restrict admin permissions to minimize security risks. Go to System > Permissions > User Roles to review and limit permissions. %1', '<a target="_blank" href="' . $this->url->getUrl('adminhtml/user/index') . '">' . __('Change') . '</a>')
            : (string)__(self::RESOLVED_MESSAGE);
    }
}
