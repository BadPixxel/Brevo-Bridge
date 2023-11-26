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

namespace BadPixxel\BrevoBridge\Services\Emails;

use BadPixxel\BrevoBridge\Dictionary\ServiceTags;
use BadPixxel\BrevoBridge\Entity\AbstractEmailStorage;
use BadPixxel\BrevoBridge\Helpers\EmailValidator;
use BadPixxel\BrevoBridge\Interfaces\HtmlTemplateProviderInterface;
use BadPixxel\BrevoBridge\Interfaces\MjmlTemplateProviderInterface;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Model\CreateSmtpEmail;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\User\UserInterface as User;


/**
 * Emails Manager: Access & Process Emails
 */
#[Autoconfigure(public: true)]
class EmailsManager
{
    use Managers\ErrorLoggerTrait;

    private static EmailsManager $staticInstance;

    /**
     * @param AbstractEmail[] $emails
     */
    public function __construct(
        private readonly Configuration  $config,
        private readonly EmailProcessor $processor,
        private readonly EmailsStorage  $storage,
        private readonly SmtpManager    $smtp,
        #[TaggedIterator(ServiceTags::EMAIL)]
        private readonly iterable       $emails,
    ) {
        //==============================================================================
        // Store Static Instance for Access as Static
        self::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     */
    public static function getInstance(): EmailsManager
    {
        return self::$staticInstance;
    }

    /**
     * Generate a Fake version of an Email
     */
    public function fake(AbstractEmail $email, User $user): ?AbstractEmail
    {
        //==============================================================================
        // Build Email using Fake Arguments
        return $this->build($email, $user, $email->getFakeArguments());
    }

    /**
     * Build Email.
     *
     * @param AbstractEmail $email The Email to Compile
     * @param User|User[] $toUsers Target User or Array of Target Users
     * @param array $args   User Inputs
     *
     * @return null|AbstractEmail
     */
    public function build(AbstractEmail $email, array|User $toUsers, array $args): ?AbstractEmail
    {
        //==============================================================================
        // Setup Email
        $email
            ->setEmail($this->smtp->newSmtpEmail())
            ->setToUsers($toUsers)
            ->configure($args)
        ;
        //==============================================================================
        // Apply Processors to Email
        $this->process($email);
        //==============================================================================
        // Validate Email & Parameters
        try {
            $resolvedParams = EmailValidator::validate($email);
        } catch (\Exception $ex) {
            return $this->setError(
                sprintf("Email Validation Fails: %s", $ex->getMessage())
            );
        }
        //==============================================================================
        // Update Parameters with resolved Values
        $email->getEmail()->setParams((object) $resolvedParams);

        return $email;
    }

    /**
     * Send an Abstract Brevo Email using Smtp Api.
     *
     * @param AbstractEmail $email The Email to Compile
     * @param User|User[] $toUsers Target User or Array of Target Users
     * @param array $args User Inputs
     * @param bool          $demoMode Are we in Debug/Demo/Test Mode
     *
     * @return null|CreateSmtpEmail
     */
    public function send(AbstractEmail $email, array|User $toUsers, array $args, bool $demoMode = false): ?CreateSmtpEmail
    {
        //==============================================================================
        // Build Email using User Arguments
        if (!$compiledEmail = $this->build($email, $toUsers, $args)) {
            return null;
        }
        //==============================================================================
        // Send Email using Smtp Api
        $sendEmail = $this->smtp->send(
            $compiledEmail->getToUsers(),
            $compiledEmail->getEmail(),
            $demoMode
        );
        if (!$sendEmail) {
            $this->setError($this->smtp->getLastError());
        }

        return $sendEmail;
    }

    /**
     * Update Email Storage from Api.
     */
    public function update(AbstractEmailStorage $storageEmail, bool $force): void
    {
        //==============================================================================
        // Update Email Events
        $this->updateEvents($storageEmail, $force);
        //==============================================================================
        // Update Email Html Contents
        $this->updateContents($storageEmail, $force);
    }

    /**
     * Find All Available Email Class
     *
     * @return array<string, AbstractEmail>
     */
    public function getAll(): array
    {
        static $list;

        if (!isset($list)) {
            $list = array();
            foreach ($this->emails as $email) {
                $list[crc32(get_class($email))] = $email;
            }
        }

        return $list;
    }

    /**
     * Find an Email Class by Class
     *
     * @param class-string $emailClass Email Class
     *
     * @return null|AbstractEmail
     */
    public function getEmail(string $emailClass): ?AbstractEmail
    {
        foreach ($this->emails as $email) {
            if (get_class($email) == $emailClass) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Find an Email Class by ID
     */
    public function getEmailById(string $emailId): ?AbstractEmail
    {
        return $this->getAll()[$emailId] ?? null;
    }

    /**
     * Check if Email provide Html Template
     *
     * @param class-string $emailClass Email Class
     *
     * @return bool
     */
    public function isTemplateProvider(string $emailClass): bool
    {
        return $this->isHtmlTemplateProvider($emailClass)
            || $this->isMjmlTemplateProvider($emailClass)
        ;
    }

    /**
     * Check if Email provide Html Template
     *
     * @param class-string $emailClass Email Class
     *
     * @return bool
     */
    public function isHtmlTemplateProvider(string $emailClass): bool
    {
        return $this->getEmail($emailClass) instanceof HtmlTemplateProviderInterface;
    }

    /**
     * Check if Email provide Mjml Template
     *
     * @param class-string $emailClass Email Class
     *
     * @return bool
     */
    public function isMjmlTemplateProvider(string $emailClass): bool
    {
        return $this->getEmail($emailClass) instanceof MjmlTemplateProviderInterface;
    }

    /**
     * Apply Processors to a Transactional Email.
     */
    private function process(AbstractEmail $email): AbstractEmail
    {
        return $this->processor->process($email);
    }

    /**
     * Update Email Events List from Smtp Api.
     */
    private function updateEvents(AbstractEmailStorage $storageEmail, bool $force): self
    {
        //==============================================================================
        // Check if Events Refresh is Allowed
        if (!$force && !$this->config->isRefreshMetadataAllowed()) {
            return $this;
        }
        //==============================================================================
        // Check if Events Refresh is Needed
        if (!$force && !$storageEmail->isEventOutdated()) {
            return $this;
        }
        //==============================================================================
        // Collect Events
        $events = $this->smtp->getEvents($storageEmail->getMessageId(), $storageEmail->getEmail());
        if (empty($events)) {
            if (!empty($storageEmail->getEvents())) {
                return $this;
            }
            //==============================================================================
            // Mark Email Events as Errored in Storage
            $this->storage->updateSendEmailEventsErrored($storageEmail);

            return $this;
        }
        //==============================================================================
        // Update Email Events in Storage
        $this->storage->updateSendEmailEvents($storageEmail, $events);

        return $this;
    }

    /**
     * Update Email Contents from Smtp Api.
     */
    private function updateContents(AbstractEmailStorage &$storageEmail, bool $force): self
    {
        //==============================================================================
        // Only if Events Refresh is Allowed
        if (!$this->config->isRefreshContentsAllowed()) {
            return $this;
        }
        //==============================================================================
        // Check if Contents Refresh is Needed
        if (!$force && !empty($storageEmail->getHtmlContent())) {
            return $this;
        }
        //==============================================================================
        // Ensure we have Email UUID
        $uuid = $storageEmail->getUuid();
        if (empty($uuid)) {
            $uuid = $this->smtp->getUuid($storageEmail->getMessageId());
        }
        if (empty($uuid)) {
            return $this;
        }
        //==============================================================================
        // Collect Html contents
        $htmlContents = $this->smtp->getContents($uuid);
        if (empty($htmlContents) || ("Mail content not available" == $htmlContents)) {
            return $this;
        }
        //==============================================================================
        // Update Storage
        $this->storage->updateSendEmailContents($storageEmail, $uuid, $htmlContents);

        return $this;
    }
}
