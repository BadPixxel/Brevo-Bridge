<?php

/*
 *  Copyright (C) 2021 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Command;

use ArrayAccess;
use BadPixxel\SendinblueBridge\Services\AccountManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Just Check Connection to User Sendinblue Api.
 */
class PingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('sendinblue:api:ping')
            ->setDescription('Test Connection to SendInBlue API')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var AccountManager $accountManager */
        $accountManager = $this->getContainer()->get(AccountManager::class);
        /** @var null|ArrayAccess $result */
        $result = $accountManager->getAccount();
        if (!$result) {
            $output->writeln(
                '<error>Exception when calling AccountApi->getAccount: '.$accountManager->getLastError().'</error>'
            );

            return -1;
        }

        $message = 'Sendinblue Connected: ';
        $message .= ' Compte '.$result['companyName'];
        foreach ($result['plan'] as $plan) {
            $message .= ' ['.ucwords($plan['type']).': '.$plan['credits'].' Credits] ';
        }
        $output->writeln('<info>'.$message.'</info>');

        return 0;
    }
}
