<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Security\Console\Command;

use Magefan\Security\Api\SecurityCheckerUpdateCacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends Command
{
    /**
     * @var SecurityCheckerUpdateCacheInterface
     */
    private $securityCheckerUpdateCache;

    /**
     * @param SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache
     */
    public function __construct(SecurityCheckerUpdateCacheInterface $securityCheckerUpdateCache)
    {
        parent::__construct();
        $this->securityCheckerUpdateCache = $securityCheckerUpdateCache;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('mfsecurity:refresh')
            ->setDescription('Refresh Magefan Security status cache')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'Refresh only this checker code')
            ->addOption('token', null, InputOption::VALUE_OPTIONAL, 'Progress-tracking token');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code  = $input->getOption('code') ?: null;
        $token = $input->getOption('token') ?: null;
        $this->securityCheckerUpdateCache->execute($code, $token);
        $output->writeln('<info>Security status refreshed.</info>');
        return 0;
    }
}
