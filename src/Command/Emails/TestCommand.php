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

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use BadPixxel\BrevoBridge\Services\Emails\EmailsStorage;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Test Command for Sending an Dummy Emails.
 */
class TestCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly EmailsManager $manager,
        private readonly EmailsStorage $storage,
    ) {
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:email:test')
            ->setDescription("Email Sending test: require user email")
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Target Email: Who should receive the tests emails'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_OPTIONAL,
                'Class of Email to send'
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
        // Identify Email to Send
        $email = $this->getEmail($input, $output);
        if (is_null($email)) {
            return 0;
        }
        //==============================================================================
        // Send a Fake Email
        $sendEmail = $email::sendDemo($user);
        if (!$sendEmail) {
            return self::showResult($output, false, $email::class, ' Email fails: '.$email::getLastError());
        }

        return self::showResult($output, true, $email::class, ' Email send !');
    }

    /**
     * Get User for Sending the Mail
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
     * Get Email Class to Send
     */
    protected function getEmail(InputInterface $input, OutputInterface $output): ?AbstractEmail
    {
        /** @var string $emailClass */
        $emailClass = $input->getOption('email');
        //==============================================================================
        // Identify Email by Class
        if (class_exists($emailClass) && ($email = $this->manager->getEmail($emailClass))) {
            return $email;
        }
        //==============================================================================
        // Ask User to Select
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select email to send',
            // choices can also be PHP objects that implement __toString() method
            $this->manager->getAll(),
            0
        );
        $email = $helper->ask($input, $output, $question);

        return ($email instanceof AbstractEmail) ? $email : null;
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
