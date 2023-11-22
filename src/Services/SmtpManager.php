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

namespace BadPixxel\BrevoBridge\Services;

use BadPixxel\BrevoBridge\Entity\AbstractEmailStorage as EmailStorage;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Exception;
use GuzzleHttp\Client;
use Symfony\Component\Routing\RouterInterface as Router;
use Symfony\Component\Security\Core\User\UserInterface as User;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;
use Twig\Environment as Twig;

/**
 * Smtp Emails Manager for SendingBlue Api.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SmtpManager
{
    use Managers\ErrorLoggerTrait;
    use Managers\TemplatingTrait;
    use Managers\StorageTrait;
    use Managers\EmailsUpdaterTrait;

    /**
     * Transactional Emails API Service.
     *
     * @var null|TransactionalEmailsApi
     */
    protected ?TransactionalEmailsApi $smtpApi;

    /**
     * @var SmtpManager
     */
    private static SmtpManager $staticInstance;

    public function __construct(
        private Configuration $config,
        private EmailProcessor $processor,
        EntityManager $doctrine,
        Twig $twig,
        Translator $translator,
        Router $router
    ) {
        //==============================================================================
        // Connect to Storage Services
        $this->setupStorage($doctrine);
        //==============================================================================
        // Connect to Templating Services
        $this->setupTemplating(
            $twig,
            $translator,
            $this->config->configureRouter($router)
        );
        //==============================================================================
        // Store Static Instance for Access as Static
        self::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     *
     * @return SmtpManager
     */
    public static function getInstance(): SmtpManager
    {
        return self::$staticInstance;
    }

    /**
     * Create a new Transactional Email.
     *
     * @return SendSmtpEmail
     */
    public function create(): SendSmtpEmail
    {
        //==============================================================================
        // Create new Smtp Email
        $newEmail = new SendSmtpEmail();
        //==============================================================================
        // Setup Default Email Values
        $newEmail
            ->setSender($this->config->getDefaultSender())
            ->setReplyTo($this->config->getDefaultReplyTo())
        ;

        return $newEmail;
    }

    /**
     * Apply Processors to a Transactional Email.
     */
    public function process(AbstractEmail $email): AbstractEmail
    {
        return $this->processor->process($email);
    }

    /**
     * Send a Transactional Email from Api.
     *
     * @param array         $toUser
     * @param SendSmtpEmail $sendEmail
     * @param bool          $demoMode
     *
     * @return null|CreateSmtpEmail
     */
    public function send(array $toUser, SendSmtpEmail $sendEmail, bool $demoMode): ?CreateSmtpEmail
    {
        try {
            //==============================================================================
            // Check if Sending Emails is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                return $this->setError('SendInBlue API is Disabled');
            }
            //==============================================================================
            // Check if THIS Email was Already Send
            $filteredUsers = $this->filterAlreadySendUsers($toUser, $sendEmail, $demoMode);
            if (!$filteredUsers) {
                return $this->setError('This Email has Already been Send...');
            }
            //==============================================================================
            // Send the Email
            $createEmail = $this->getApi()->sendTransacEmail($sendEmail);
            //==============================================================================
            // Save the Email to DataBase
            $this->saveSendEmail($filteredUsers, $sendEmail, $createEmail);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return $createEmail;
    }

    /**
     * Generate a Fake version of an Email
     *
     * @param class-string $emailClass
     * @param User         $user
     *
     * @return null|AbstractEmail
     */
    public function fake(string $emailClass, User $user): ?AbstractEmail
    {
        //==============================================================================
        // Safety Checks
        if (!is_subclass_of($emailClass, AbstractEmail::class)) {
            return $this->setError("Email Class must be a ".AbstractEmail::class);
        }
        //==============================================================================
        // Generate a Demo Instance of the Email
        $fakeEmail = $emailClass::getDemoInstance($user);

        //==============================================================================
        // Apply Processors to the Email
        return $this->processor->process($fakeEmail);
    }

    /**
     * Update Email Storage from Api.
     *
     * @param EmailStorage $storageEmail
     * @param bool         $force
     *
     * @return void
     */
    public function update(EmailStorage $storageEmail, bool $force): void
    {
        //==============================================================================
        // Update Email Events
        $this->updateEvents($storageEmail, $force);
        //==============================================================================
        // Update Email Html Contents
        $this->updateContents($storageEmail, $force);
    }

    /**
     * Find an Email Class by Code
     *
     * @param string $emailCode
     *
     * @return null|string
     */
    public function getEmailByCode(string $emailCode): ?string
    {
        return $this->config->getEmailByCode($emailCode);
    }

    /**
     * Find All Available Email Class
     *
     * @return array
     */
    public function getAllEmails(): array
    {
        return $this->config->getAllEmails();
    }

    /**
     * Get Configuration
     *
     * @return Configuration
     */
    protected function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Access to SendinBlue API Service.
     *
     * @return TransactionalEmailsApi
     */
    private function getApi(): TransactionalEmailsApi
    {
        if (!isset($this->smtpApi)) {
            $this->smtpApi = new TransactionalEmailsApi(
                new Client(),
                $this->config->getSdkConfig()
            );
        }

        return $this->smtpApi;
    }
}
