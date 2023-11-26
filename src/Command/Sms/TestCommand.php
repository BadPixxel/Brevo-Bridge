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

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Services\Emails\EmailsStorage;
use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use BadPixxel\BrevoBridge\Services\Sms\SmsStorage;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Test Command for Sending a Dummy Sms.
 */
class TestCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly SmsManager $manager,
        private readonly SmsStorage $storage,
    )
    {
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:sms:test')
            ->setDescription("Sms Sending test: require user email")
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Target Email: Who should receive the tests sms'
            )
            ->addOption(
                'send',
                null,
                InputOption::VALUE_OPTIONAL,
                'Class of Sms to send'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //==============================================================================
        // Identify User in Database
        $user = $this->getUser($input, $output);
        if (is_null($user)) {
            return 0;
        }
        //==============================================================================
        // Identify Sms to Send
        $sms = $this->getSms($input, $output);
        if (is_null($sms)) {
            return 0;
        }

        //==============================================================================
        // Send a Fake Email
        $sendSms = $sms::sendDemo($user);
        if (!$sendSms) {
            return self::showResult($output, false, $sms::class, ' Sms fails: '.$sms::getLastError());
        }

        return self::showResult($output, true, $sms::class, ' Sms send !');
   }

    /**
     * Get User for Sending the Sms
     */
    protected function getUser(InputInterface $input, OutputInterface $output): ?UserInterface
    {
        /** @var string $targetEmail */
        $targetEmail = $input->getArgument('user');
        //==============================================================================
        // Identify User in Database
        $user = $this->storage->getUserByEmail($targetEmail);
        if (is_null($user)) {
            self::showResult($output, false, 'Init', 'Unable to identify User');

            return null;
        }

        return $user;
    }

    /**
     * Get Sms Class to Send
     */
    protected function getSms(InputInterface $input, OutputInterface $output): ?AbstractSms
    {
        /** @var string $smsClass */
        $smsClass = $input->getOption('sms');
        //==============================================================================
        // Identify Sms by Class
        if (class_exists($smsClass) && ($sms = $this->manager->getSms($smsClass))) {
            return $sms;
        }
        //==============================================================================
        // Ask User to Select
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select sms to send',
            // choices can also be PHP objects that implement __toString() method
            $this->manager->getAll(),
            0
        );
        $sms = $helper->ask($input, $output, $question);

        return ($sms instanceof AbstractSms) ? $sms : null;
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
