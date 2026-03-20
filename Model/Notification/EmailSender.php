<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Model\Notification;

use Magefan\Security\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilderFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class EmailSender
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilderFactory
     */
    private $transportBuilderFactory;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param TransportBuilderFactory $transportBuilderFactory
     * @param StateInterface $inlineTranslation
     * @param Emulation $emulation
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        TransportBuilderFactory $transportBuilderFactory,
        StateInterface $inlineTranslation,
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Send notification email for newly detected security issues
     *
     * @param array $newIssueNames List of issue name strings
     * @return void
     */
    public function send(array $newIssueNames): void
    {
        if (empty($newIssueNames)) {
            return;
        }

        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            return;
        }

        $this->inlineTranslation->suspend();
        $this->emulation->startEnvironmentEmulation(Store::DEFAULT_STORE_ID, Area::AREA_FRONTEND, true);

        try {
            $store = $this->storeManager->getStore(Store::DEFAULT_STORE_ID);

            $transportBuilder = $this->transportBuilderFactory->create();
            $transportBuilder
                ->setTemplateIdentifier($this->config->getNotificationEmailTemplate())
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID,
                ])
                ->setTemplateVars([
                    'store_name' => $store->getName(),
                    'store_url'  => $store->getBaseUrl(),
                    'issues'     => $newIssueNames,
                ])
                ->setFromByScope($this->config->getNotificationEmailSender());

            foreach ($recipients as $email) {
                $transportBuilder->addTo($email);
            }

            $transportBuilder->getTransport()->sendMessage();
        } catch (\Exception $e) {

            var_dump($e->getMessage());
            echo 'aaaaaaaaaaaa';exit();

            $this->logger->error(
                'Magefan Security: failed to send issue notification email. ' . $e->getMessage()
            );
        }

        $this->emulation->stopEnvironmentEmulation();
        $this->inlineTranslation->resume();
    }

    /**
     * Parse comma-separated recipients from config
     *
     * @return string[]
     */
    private function getRecipients(): array
    {
        $result = [];
        foreach (explode(',', $this->config->getNotificationEmailRecipients()) as $email) {
            $email = trim($email);
            if ($email) {
                $result[] = $email;
            }
        }
        return $result;
    }
}
