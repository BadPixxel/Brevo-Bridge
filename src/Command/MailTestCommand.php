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

use BadPixxel\SendinblueBridge\Models\AbstractEmail;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use FOS\UserBundle\Model\UserInterface as User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Command fro Sending an Dummy Sms.
 */
class MailTestCommand extends ContainerAwareCommand
{
    /**
     * @var SmtpManager
     */
    private $smtpManager;

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('sendinblue:email:test')
            ->setDescription("Email Sending test: require email & ")
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Target Email: Who should receive the tests emails'
            )
            ->addOption(
                'send',
                null,
                InputOption::VALUE_REQUIRED,
                'Email to send (\"all\" to send all regietered emails)'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SmtpManager $smtpManager */
        $smtpManager = $this->getContainer()->get('badpixxel.sendinblue.smtp');
        $this->smtpManager = $smtpManager;

        /** @var string $targetEmail */
        $targetEmail = $input->getArgument('target');
        //==============================================================================
        // Identify User in Database
        $user = $smtpManager->getUserByEmail($targetEmail);
        if (is_null($user)) {
            return self::showResult($output, false, 'Init', 'Unable to identify User');
        }
        //==============================================================================
        // Send All Avalaible Emails
        /** @var string $action */
        $action = $input->getOption('send');
        if ("all" == $action) {
            foreach (array_keys($smtpManager->getAllEmails()) as $emailCode) {
                if (null != $this->sendEmail($user, $emailCode, $output)) {
                    return -1;
                }
            }

            return null;
        }

        //==============================================================================
        // Send Only One Email by Code
        return $this->sendEmail($user, $action, $output);
    }

    /**
     * Test Sending an Email By Code
     *
     * @param string          $emailCode
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function sendEmail(User $user, string $emailCode, OutputInterface $output): ?int
    {
        //==============================================================================
        // Identify Email Class
        $emailClass = $this->smtpManager->getEmailByCode($emailCode);
        if (is_null($emailClass)) {
            return self::showResult($output, false, $emailCode, 'Unable to identify Email: '.$emailCode);
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            return self::showResult($output, false, $emailCode, 'Email Class: '.$emailClass.' was not found');
        }
        if (!is_subclass_of($emailClass, AbstractEmail::class)) {
            return self::showResult(
                $output,
                false,
                $emailCode,
                'Email Class: '.$emailCode.' is not an '.AbstractEmail::class
            );
        }
        //==============================================================================
        // Send Test Email
        $email = $emailClass::sendDemo($user);
        if (is_null($email)) {
            return self::showResult(
                $output,
                false,
                $emailCode,
                'Exception => '.$emailClass::getLastError()
            );
        }
        //==============================================================================
        // Extract User Name
        $username = method_exists($user, "__toString")
            ? $user->__toString()
            : $user->getUsername();

        return self::showResult($output, true, $emailCode, ' Email send to '.$username);
    }

    /**
     * Render result in Console
     *
     * @param OutputInterface $output
     * @param bool            $result
     * @param string          $code
     * @param string          $text
     *
     * @return null|int
     */
    protected static function showResult(OutputInterface $output, bool $result, string $code, string $text): ?int
    {
        $status = $result ? '[<info> OK </info>]' : '[<error> KO </error>]';
        $output->writeln($status.' '.ucfirst($code).' : '.$text);

        return $result ? null : -1;
    }
}
