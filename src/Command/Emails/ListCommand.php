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

namespace BadPixxel\BrevoBridge\Command\Emails;

use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show list of All Configured Emails.
 */
class ListCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly EmailsManager $manager
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:email:list')
            ->setDescription("[Brevo] List all available emails")
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
        $table->setHeaders(array('ID', 'Class', 'Templated', 'Provide'));
        //==============================================================================
        // Walk on All Available Emails
        foreach ($this->manager->getAll() as $serviceId => $abstractEmail) {
            $class = get_class($abstractEmail);
            $usesTemplate = $this->manager->isTemplateProvider($class);
            $templateProvider = false;
            if ($this->manager->isMjmlTemplateProvider($class)) {
                $templateProvider = "<comment>MJML</comment>";
            } elseif ($this->manager->isHtmlTemplateProvider($class)) {
                $templateProvider = "<comment>HTML</comment>";
            }

            $table->addRow(array(
                $serviceId,
                get_class($abstractEmail),
                $usesTemplate ? "<info>Yes</info>" : "<comment>No</comment>",
                $templateProvider,
            ));
        }
        //==============================================================================
        // Render Console Table
        $table->render();

        return 0;
    }
}
