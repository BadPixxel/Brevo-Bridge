<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Command;

use BadPixxel\SendinblueBridge\Services\SmtpManager;
use BadPixxel\SendinblueBridge\Templates\TestUserEmail;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\User;

/**
 * Test Command fro Sending an Dummy Sms.
 */
class MailTestCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    const TEST_MSG = 'IMMO-POP : '
            ."Ceci est un message de test!\n"
            .'Merci de ne pas y répondre.';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('sendinblue:email:test')
            // the short description shown while running "php bin/console list"
            ->setDescription("Teste l'envoi d'un Email")
            // le numéro de téléphone auquel envoyer un SMS
            ->addArgument('mail', InputArgument::REQUIRED, 'Email destinataire')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SmtpManager $accountEndpoint */
        $smtpManager = $this->getContainer()->get('badpixxel.sendinblue.smtp');
        //==============================================================================
        // Identify User in Database
        $user = $smtpManager->getUserByEmail($input->getArgument('mail'));
        if (is_null($user)) {
            $output->writeln(
                '<error>Unable to identify User by email: '.$input->getArgument('mail').'</error>'
            );

            return -1;
        }
        //==============================================================================
        // Send Test Email
        $email = \Application\SendinblueBridge\Emails\BasicNotification::sendDemo($user);
        if (is_null($email)) {
            $output->writeln(
                '<error>Exception when sending your email: '.TestUserEmail::getLastError().'</error>'
            );

            return -1;
        }
        $output->writeln('<info>Email was send to '.$input->getArgument('mail').'</info>');

        return null;
    }
}
