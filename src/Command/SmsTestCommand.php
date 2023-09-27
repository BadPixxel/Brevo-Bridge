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

use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Services\SmsManager;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Command for Sending an Dummy Sms.
 */
class SmsTestCommand extends Command
{
    /**
     * @var SmsManager
     */
    private SmsManager $smsManager;

    /**
     * Command Constructor
     *
     * @param SmsManager  $smsManager
     * @param null|string $name
     */
    public function __construct(SmsManager $smsManager, string $name = null)
    {
        $this->smsManager = $smsManager;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:sms:test')
            ->setDescription("Sms Sending test: require user email & sms Code")
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Target Email: Who should receive the tests emails'
            )
            ->addOption(
                'send',
                null,
                InputOption::VALUE_REQUIRED,
                'Sms to send (\"all\" to send all registered sms)'
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
        $user = $this->smsManager->getUserByEmail($targetEmail);
        if (is_null($user)) {
            return self::showResult($output, false, 'Init', 'Unable to identify User');
        }
        //==============================================================================
        // Send All Available Sms
        /** @var string $action */
        $action = $input->getOption('send');
        if ("all" == $action) {
            foreach (array_keys($this->smsManager->getAllSms()) as $emailCode) {
                if (null != $this->sendSms($user, $emailCode, $output)) {
                    return -1;
                }
            }

            return 0;
        }

        //==============================================================================
        // Send Only One Sms by Code
        return $this->sendSms($user, $action, $output);
    }

    /**
     * Test Sending an Sms By Code
     *
     * @param User            $user
     * @param string          $smsCode
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function sendSms(User $user, string $smsCode, OutputInterface $output): int
    {
        //==============================================================================
        // Identify Sms Class
        $smsClass = $this->smsManager->getSmsByCode($smsCode);
        if (is_null($smsClass)) {
            return self::showResult($output, false, $smsCode, 'Unable to identify Sms: '.$smsCode);
        }
        //==============================================================================
        // Verify Sms Class
        if (!class_exists($smsClass)) {
            return self::showResult($output, false, $smsCode, 'Sms Class: '.$smsClass.' was not found');
        }
        if (!is_subclass_of($smsClass, AbstractSms::class)) {
            return self::showResult(
                $output,
                false,
                $smsCode,
                'Sms Class: '.$smsCode.' is not an '.AbstractSms::class
            );
        }
        //==============================================================================
        // Send Test Sms
        $sms = $smsClass::sendDemo($user);
        if (is_null($sms)) {
            return self::showResult(
                $output,
                false,
                $smsCode,
                'Exception => '.$smsClass::getLastError()
            );
        }
        //==============================================================================
        // Extract User Name
        $username = method_exists($user, "__toString")
            ? $user->__toString()
            : $user->getUserIdentifier();

        return self::showResult($output, true, $smsCode, ' Sms send to '.$username);
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
