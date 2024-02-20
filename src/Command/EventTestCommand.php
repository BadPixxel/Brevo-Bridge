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

use BadPixxel\BrevoBridge\Models\AbstractTrackEvent;
use BadPixxel\BrevoBridge\Services\Emails\EmailsStorage;
use BadPixxel\BrevoBridge\Services\Events\EventManager;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Command for Sending an Dummy Event.
 */
class EventTestCommand extends Command
{
    /**
     * Command Constructor
     */
    public function __construct(
        private readonly EmailsStorage $storage,
        private readonly EventManager $eventManager
    ) {
        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this
            ->setName('brevo:event:test')
            ->setDescription("Tracker Event Sending test: require user email & event Code")
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'User Email for this event'
            )
            ->addOption(
                'send',
                null,
                InputOption::VALUE_REQUIRED,
                'Event Code to send (\"all\" to send all registered events)'
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
        $user = $this->storage->getUserByEmail($targetEmail);
        if (is_null($user)) {
            return self::showResult($output, false, 'Init', 'Unable to identify User');
        }
        //==============================================================================
        // Send All Available Events
        /** @var string $action */
        $action = $input->getOption('send');
        if ("all" == $action) {
            foreach (array_keys($this->eventManager->getAllEvents()) as $eventCode) {
                if (null != $this->sendEvent($user, $eventCode, $output)) {
                    return -1;
                }
            }

            return 0;
        }

        //==============================================================================
        // Send Only One Event by Code
        return $this->sendEvent($user, $action, $output);
    }

    /**
     * Test Sending an Event By Code
     *
     * @param User            $user
     * @param string          $eventCode
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function sendEvent(User $user, string $eventCode, OutputInterface $output): int
    {
        //==============================================================================
        // Identify Event Class
        $eventClass = $this->eventManager->getEventByCode($eventCode);
        if (is_null($eventClass)) {
            return self::showResult($output, false, $eventCode, 'Unable to identify Event: '.$eventCode);
        }
        //==============================================================================
        // Verify Event Class
        if (!class_exists($eventClass)) {
            return self::showResult($output, false, $eventCode, 'Event Class: '.$eventClass.' was not found');
        }
        if (!is_subclass_of($eventClass, AbstractTrackEvent::class)) {
            return self::showResult(
                $output,
                false,
                $eventCode,
                'Event Class: '.$eventCode.' is not an '.AbstractTrackEvent::class
            );
        }
        //==============================================================================
        // Send Test Event
        $success = $eventClass::sendDemo($user);
        if (!$success) {
            return self::showResult(
                $output,
                false,
                $eventCode,
                'Exception => '.$eventClass::getLastError()
            );
        }
        //==============================================================================
        // Extract User Name
        $username = method_exists($user, "__toString")
            ? $user->__toString()
            : $user->getUserIdentifier();

        return self::showResult($output, true, $eventCode, ' Event for '.$username.' submitted');
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
