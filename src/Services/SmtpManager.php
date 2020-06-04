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

namespace BadPixxel\SendinblueBridge\Services;

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as EmailStorage;
use BadPixxel\SendinblueBridge\Services\ConfigurationManager as Configuration;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Exception;
use FOS\UserBundle\Model\UserInterface as User;
use FOS\UserBundle\Model\UserManagerInterface as UserManager;
use SendinBlue\Client\Api\SMTPApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Model\CreateSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmail;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Twig;
use Symfony\Component\Routing\RouterInterface as Router;
use Symfony\Component\Translation\TranslatorInterface as Translator;

/**
 * Smtp Emails Manager for SendingBlue Api.
 */
class SmtpManager
{
    use \BadPixxel\SendinblueBridge\Models\Managers\ErrorLoggerTrait;
    use \BadPixxel\SendinblueBridge\Models\Managers\UserFinderTrait;
    use \BadPixxel\SendinblueBridge\Models\Managers\TemplatingTrait;
    use \BadPixxel\SendinblueBridge\Models\Managers\StorageTrait;
    use \BadPixxel\SendinblueBridge\Models\Managers\EmailsUpdaterTrait;

    /**
     * Smtp API Service.
     *
     * @var SMTPApi
     */
    protected $smtpApi;

    /**
     * Bridge Configuration.
     *
     * @var ConfigurationManager
     */
    private $config;

    /**
     * @var SmtpManager
     */
    private static $staticInstance;

    /**
     * @param SMTPApi       $api
     * @param Configuration $config
     * @param EntityManager $doctrine
     * @param UserManager   $users
     * @param Twig          $twig
     * @param Translator    $translator
     */
    public function __construct(
        SMTPApi $api,
        Configuration $config,
        EntityManager $doctrine,
        UserManager $users,
        Twig $twig,
        Translator $translator,
        Router $router
    ) {
        //==============================================================================
        // Connect to Bridge Configuration Service
        $this->config = $config;
        //==============================================================================
        // Connect to Smtp API Service
        $this->smtpApi = $api;
        //==============================================================================
        // Connect to FOS User Manager
        $this->setUserManager($users);
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
        static::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     *
     * @return SmtpManager
     */
    public static function getInstance(): SmtpManager
    {
        return static::$staticInstance;
    }

    /**
     * Access to SendinBlue API Service.
     *
     * @return SMTPApi
     */
    public function getApi(): SMTPApi
    {
        return $this->smtpApi;
    }

    /**
     * Create a new Transactionnal Email.
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
     * Send a Transactionnal Email from Api.
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
            $createEmail = $this->smtpApi->sendTransacEmail($sendEmail);
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
}
