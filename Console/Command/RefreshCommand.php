<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Console\Command;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Magefan\Security\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends Command
{
    public const COMMAND_NAME = 'magefan:security:refresh';

    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var State
     */
    private $state;

    /**
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     * @param Config $config
     * @param State $state
     */
    public function __construct(
        SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache,
        Config $config,
        State $state
    ) {
        parent::__construct();
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
        $this->config = $config;
        $this->state = $state;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Refresh Magefan Security status cache')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'Refresh only this checker code')
            ->addOption('token', null, InputOption::VALUE_OPTIONAL, 'Progress-tracking token');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->config->isEnabled()) {
            try {
                $this->state->setAreaCode(Area::AREA_GLOBAL);
            } catch (LocalizedException $e) {
                $output->writeln((string)__('Something went wrong. %1', $this->escaper->escapeHtml($e->getMessage())));
            }

            $code  = $input->getOption('code') ?: null;
            $token = $input->getOption('token') ?: null;
            $this->securityCheckerUpdateCache->execute($code, $token);
            $output->writeln('<info>Security status refreshed.</info>');
        } else {
            $output->writeln("Magefan Security Extension is disabled. Please turn on it.");
        }

        return 0;
    }
}
