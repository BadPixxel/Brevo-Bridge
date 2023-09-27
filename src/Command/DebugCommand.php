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

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Services\ConfigurationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Debug for Sendinblue Api Configuration.
 */
class DebugCommand extends Command
{
    /**
     * Bridge Configuration.
     *
     * @var ConfigurationManager
     */
    private $config;

    /**
     * Command Constructor
     *
     * @param ConfigurationManager $config
     * @param null|string          $name
     */
    public function __construct(ConfigurationManager $config, string $name = null)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:api:debug')
            ->setDescription('Show Brevo API Configuration Details')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showAllEmailsType($output);
        $this->showAllSmsType($output);
        $this->showAllEventsType($output);

        return 0;
    }

    /**
     * Show All Emails Types Table
     *
     * @param OutputInterface $output
     */
    private function showAllEmailsType(OutputInterface $output): void
    {
        //====================================================================//
        // Load List of Available Emails
        $emails = $this->config->getAllEmails();
        $output->writeln(sprintf('<comment>Found %s Email Classes</comment>', count($emails)));
        if (empty($emails)) {
            return;
        }
        //====================================================================//
        // Show Emails List
        $table = new Table($output);
        $table->setHeaders(array('Code', 'Status', 'Class'));
        foreach ($emails as $code => $emailClass) {
            $table->addRow(array(
                $code,
                class_exists($emailClass) && is_subclass_of($emailClass, AbstractEmail::class)
                    ? "<info>Ok</info>"
                    : "<error>Ko</error>",
                $emailClass
            ));
        }
        $table->render();
    }

    /**
     * Show All Sms Types Table
     *
     * @param OutputInterface $output
     */
    private function showAllSmsType(OutputInterface $output): void
    {
        //====================================================================//
        // Load List of Available Sms
        $allSms = $this->config->getAllSms();
        $output->writeln(sprintf('<comment>Found %s Sms Classes</comment>', count($allSms)));
        if (empty($allSms)) {
            return;
        }
        //====================================================================//
        // Show Emails List
        $table = new Table($output);
        $table->setHeaders(array('Code', 'Status', 'Class'));
        foreach ($allSms as $code => $smsClass) {
            $table->addRow(array(
                $code,
                class_exists($smsClass) && is_subclass_of($smsClass, AbstractSms::class)
                    ? "<info>Ok</info>"
                    : "<error>Ko</error>",
                $smsClass
            ));
        }
        $table->render();
    }

    /**
     * Show All Events Types Table
     *
     * @param OutputInterface $output
     */
    private function showAllEventsType(OutputInterface $output): void
    {
        //====================================================================//
        // Load List of Available Events
        $events = $this->config->getAllEvents();
        $output->writeln(sprintf('<comment>Found %s Event Classes</comment>', count($events)));
        if (empty($events)) {
            return;
        }
        //====================================================================//
        // Show Events List
        $table = new Table($output);
        $table->setHeaders(array('Code', 'Class'));
        foreach ($events as $code => $eventClass) {
            $table->addRow(array(
                $code,
                $eventClass
            ));
        }
        $table->render();
    }
}
