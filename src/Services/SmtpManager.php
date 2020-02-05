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
     * @param SMTPApi $smtpApi
     */
    public function __construct(SMTPApi $smtpApi, Configuration $config, EntityManager $doctrine, UserManager $userManager, Twig $twig, Translator $translator)
    {
        //==============================================================================
        // Connect to Smtp API Service
        $this->smtpApi = $smtpApi;
        //==============================================================================
        // Connect to FOS User Manager
        $this->setUserManager($userManager);
        //==============================================================================
        // Connect to Storage Services
        $this->setupStorage($doctrine);
        //==============================================================================
        // Connect to Templating Services
        $this->setupTemplating($twig, $translator);
        //==============================================================================
        // Connect to Bridge Configuration Service
        $this->config = $config;
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
            ->setReplyTo($this->config->getDefaultSender())
        ;

        return $newEmail;
    }

    /**
     * Send a Transactionnal Email from Api.
     *
     * @return null|CreateSmtpEmail
     */
    public function send(User $toUser, SendSmtpEmail $sendEmail, bool $demoMode): ?CreateSmtpEmail
    {
        try {
            //==============================================================================
            // Check if Sending Emails is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                $this->isAlreadySend($toUser, $sendEmail);

                return $this->setError('SendInBlue API is Disabled');
            }
            //==============================================================================
            // Check if THIS Email was Already Send
            if (!$demoMode && $this->isAlreadySend($toUser, $sendEmail)) {
                return $this->setError('This Email has Already been Send...');
            }
            //==============================================================================
            // Send the Email
            $createEmail = $this->smtpApi->sendTransacEmail($sendEmail);
            //==============================================================================
            // Save the Email to DataBase
            $this->saveSendEmail($toUser, $sendEmail, $createEmail);

            return $createEmail;
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return null;
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
}
