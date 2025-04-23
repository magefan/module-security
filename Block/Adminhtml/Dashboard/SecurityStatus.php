<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Block\Adminhtml\Dashboard;

use Magefan\Security\Api\SecurityCheckerInterface;
use Magefan\Security\Model\SecurityChecker;
use Magento\Backend\Block\Template\Context;
use Magefan\Security\Model\Config;

class SecurityStatus extends \Magento\Backend\Block\Template
{

    /**
     * @var SecurityChecker
     */
    private $securityChecker;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param SecurityChecker $securityChecker
     * @param Config $config
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        SecurityChecker $securityChecker,
        Config          $config,
        Context         $context,
        array           $data = []
    ) {
        $this->securityChecker = $securityChecker;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @return false|string
     */
    public function getSecurityStatus()
    {
        $securityStates = $this->securityChecker->execute();
        return $this->sort($securityStates);
    }

    /**
     * @param $securityStates
     * @return false|string
     */
    public function sort($securityStates)
    {
        usort($securityStates, function ($a, $b) {
            if ($a->getType() === $b->getType()) {
                return $a->getPosition() - $b->getPosition();
            }
            return $a->getType() - $b->getType();
        });

        $grouped = [];
        foreach ($securityStates as $object) {
            $type = $object->getType() ?? SecurityCheckerInterface::CANT_CHECK;
            if (!isset($grouped[$type])) {
                $grouped[$type] = 0;
            }
            $grouped[$type]++;
        }

        $labels = [
            SecurityCheckerInterface::CRITICAL => [
                'name' => 'Critical',
                'color' => '#ff6384',
            ],
            SecurityCheckerInterface::NOTICE => [
                'name' => 'Notice',
                'color' => '#ffcd56',
            ],
            SecurityCheckerInterface::CANT_CHECK => [
                'name' => 'Can\'t Check',
                'color' => 'gray',
            ],
            SecurityCheckerInterface::OK => [
                'name' => 'Resolved',
                'color' => '#4bc0c0',
            ],
        ];

        $output = [];
        foreach ($labels as $key => $info) {
            if (isset($grouped[$key])) {
                $output[] = [
                    'name' => $info['name'],
                    'value' => $grouped[$key],
                    'color' => $info['color'],
                ];
            }
        }

        return json_encode(["catagories" => $output], JSON_PRETTY_PRINT);
    }
}
