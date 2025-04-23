<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\Security;

use Magefan\Security\Api\SecurityCheckerInterface;
use Magefan\Security\Model\SecurityChecker;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;

class Dashboard extends \Magento\Backend\Block\Template
{
    /**
     * @var SecurityChecker
     */
    private $securityChecker;

    /**
     * Constructor
     *
     * @param SecurityChecker $securityChecker
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        SecurityChecker $securityChecker,
        Context             $context,
        array               $data = []
    ) {
        $this->securityChecker = $securityChecker;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject
     */
    public function getSecurityIssues()
    {
        return $this->sort($this->securityChecker->execute());
    }

    /**
     * @param $securityStates
     * @return DataObject
     */
    public function sort($securityStates)
    {
        usort($securityStates, function ($a, $b) {
            if ($a->getType() === $b->getType()) {
                return $a->getPosition() - $b->getPosition();
            }
            return $a->getType() - $b->getType();
        });

        $state = new DataObject([
            SecurityCheckerInterface::CRITICAL => 0,
            SecurityCheckerInterface::NOTICE => 0,
            SecurityCheckerInterface::CANT_CHECK => 0,
            SecurityCheckerInterface::OK => 0,
            'critical_percent' => 0,
            'notice_percent' => 0,
            'cant_check_percent' => 0,
            'resolved_percent' => 0,
            'state' => $securityStates
        ]);

        foreach ($securityStates as $object) {
            $type = $object->getType() ?? SecurityCheckerInterface::CANT_CHECK;
            $state->setData($type, $state->getData($type) + 1);
        }

        $totalIssues = $state->getData(SecurityCheckerInterface::CRITICAL) +
            $state->getData(SecurityCheckerInterface::NOTICE) +
            $state->getData(SecurityCheckerInterface::CANT_CHECK) +
            $state->getData(SecurityCheckerInterface::OK);

        if ($totalIssues != 0) {
            $state->setData('critical_percent', ($state->getData(SecurityCheckerInterface::CRITICAL) / $totalIssues) * 100);
            $state->setData('notice_percent', ($state->getData(SecurityCheckerInterface::NOTICE) / $totalIssues) * 100);
            $state->setData('cant_check_percent', ($state->getData(SecurityCheckerInterface::CANT_CHECK) / $totalIssues) * 100);
            $state->setData('resolved_percent', ($state->getData(SecurityCheckerInterface::OK) / $totalIssues) * 100);
        }

        return $state;
    }
}
