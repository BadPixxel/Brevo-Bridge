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

namespace BadPixxel\BrevoBridge\Command\Sms;

use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show list of All Configured Sms.
 */
class ListCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly SmsManager $manager
    ){
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:sms:list')
            ->setDescription("[Brevo] List all available text messages")
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //==============================================================================
        // Init Console Table
        $table = new Table($output);
        $table->setHeaders(['ID', 'Class']);
        //==============================================================================
        // Walk on All Available SMS
        foreach ($this->manager->getAll() as $serviceId => $abstractSms) {
            $table->addRow(array(
                $serviceId,
                get_class($abstractSms),
            ));
        }
        //==============================================================================
        // Render Console Table
        $table->render();

        return 0;
    }
}
