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
use BadPixxel\BrevoBridge\Services\SmtpManager;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Command for Sending an Dummy Emails.
 */
class MailTestCommand extends Command
{
    /**
     * @var SmtpManager
     */
    private $smtpManager;

    /**
     * Command Constructor
     *
     * @param SmtpManager $smtpManager
     * @param null|string $name
     */
    public function __construct(SmtpManager $smtpManager, string $name = null)
    {
        $this->smtpManager = $smtpManager;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:email:test')
            ->setDescription("Email Sending test: require user email & email Code")
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Target Email: Who should receive the tests emails'
            )
            ->addOption(
                'send',
                null,
                InputOption::VALUE_REQUIRED,
                'Email to send (\"all\" to send all registered emails)'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $targetEmail */
        $targetEmail = $input->getArgument('target');
        //==============================================================================
        // Identify User in Database
        $user = $this->smtpManager->getUserByEmail($targetEmail);
        if (is_null($user)) {
            return self::showResult($output, false, 'Init', 'Unable to identify User');
        }
        //==============================================================================
        // Send All Available Emails
        /** @var string $action */
        $action = $input->getOption('send');
        if ("all" == $action) {
            foreach (array_keys($this->smtpManager->getAllEmails()) as $emailCode) {
                if (null != $this->sendEmail($user, $emailCode, $output)) {
                    return -1;
                }
            }

            return 0;
        }

        //==============================================================================
        // Send Only One Email by Code
        return $this->sendEmail($user, $action, $output);
    }

    /**
     * Test Sending an Email By Code
     *
     * @param User            $user
     * @param string          $emailCode
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function sendEmail(User $user, string $emailCode, OutputInterface $output): int
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
            : $user->getUserIdentifier();

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
     * @return int
     */
    protected static function showResult(OutputInterface $output, bool $result, string $code, string $text): int
    {
        $status = $result ? '[<info> OK </info>]' : '[<error> KO </error>]';
        $output->writeln($status.' '.ucfirst($code).' : '.$text);

        return $result ? 0 : -1;
    }
}
