<?php

/*
 *  Copyright (C) BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\BrevoBridge\Command;

use ArrayAccess;
use BadPixxel\BrevoBridge\Services\AccountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Just Check Connection to User Sendinblue Api.
 */
class PingCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly AccountManager $accountManager
    ){
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:api:ping')
            ->setDescription('Test Connection to Brevo API')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var null|ArrayAccess $result */
        $result = $this->accountManager->getAccount();
        if (!$result) {
            $output->writeln(
                '<error>Exception when calling AccountApi->getAccount: '
                .$this->accountManager->getLastError().'</error>'
            );

            return -1;
        }

        $message = 'Brevo Connected: ';
        $message .= ' Compte '.$result['companyName'];
        /** @var array $plan */
        foreach (is_iterable($result['plan']) ? $result['plan'] : array() as $plan) {
            $message .= ' ['.ucwords($plan['type']).': '.$plan['credits'].' Credits] ';
        }
        $output->writeln('<info>'.$message.'</info>');

        return 0;
    }
}
