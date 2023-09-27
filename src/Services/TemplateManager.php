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

use BadPixxel\BrevoBridge\Helpers\MjmlConverter;
use BadPixxel\BrevoBridge\Interfaces\HtmlTemplateProviderInterface;
use BadPixxel\BrevoBridge\Interfaces\MjmlTemplateProviderInterface;
use BadPixxel\BrevoBridge\Models\Managers\ErrorLoggerTrait;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\UpdateSmtpTemplate;
use Exception;
use GuzzleHttp\Client;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * Emails Templates Manager for Brevo Api.
 */
class TemplateManager
{
    use ErrorLoggerTrait;

    /**
     * Smtp API Service.
     *
     * @var null|TransactionalEmailsApi
     */
    protected $smtpApi;

    /**
     * Bridge Configuration.
     *
     * @var ConfigurationManager
     */
    private $config;

    /**
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        //==============================================================================
        // Connect to Bridge Configuration Service
        $this->config = $config;
    }

    //==============================================================================
    // EMAILS TEMPLATES MANAGER FUNCTIONS
    //==============================================================================

    /**
     * Check if an Email Class Implement Template Management.
     *
     * @param string $emailClass
     *
     * @return bool
     */
    public function isTemplateAware(string $emailClass): bool
    {
        return is_subclass_of($emailClass, HtmlTemplateProviderInterface::class);
    }

    /**
     * Compile Email Template to raw Html Contents.
     *
     * @param string $emailClass
     *
     * @return null|string
     */
    public function compile(string $emailClass): ?string
    {
        //==============================================================================
        // Compile Email From Mjml Twig Template
        if (is_subclass_of($emailClass, MjmlTemplateProviderInterface::class)) {
            $rawMjml = $emailClass::getTemplateMjml();
            if (null == $rawMjml) {
                return $this->setError("Error Reading Mjml Template Contents");
            }

            return $this->convertMjmltoHtml($rawMjml);
        }
        //==============================================================================
        // Compile Email From Html Twig Template
        if (is_subclass_of($emailClass, HtmlTemplateProviderInterface::class)) {
            return $emailClass::getTemplateHtml();
        }

        return null;
    }

    /**
     * Update Email Html Template via SendInBlue API.
     *
     * @param string $emailClass
     * @param string $htmlTemplate
     *
     * @return null|True
     */
    public function update(string $emailClass, string $htmlTemplate): ?bool
    {
        //==============================================================================
        // Safety Checks
        if (!is_subclass_of($emailClass, HtmlTemplateProviderInterface::class)) {
            return $this->setError("Email does not manage Html Templates");
        }

        try {
            //==============================================================================
            // Create Update Template Class
            $updateTmpl = new UpdateSmtpTemplate(array("htmlContent" => $htmlTemplate));
            //==============================================================================
            // Update the Email Template
            $this->getApi()->updateSmtpTemplate(
                (int) $emailClass::getTemplateId(),
                $updateTmpl
            );
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return true;
    }

    //==============================================================================
    // TEMPLATES PARAMETERS
    //==============================================================================

    /**
     * Build Parameters for Debug Email Display
     *
     * @param string $emailClass
     *
     * @return array
     */
    public function getTmplParameters(string $emailClass, User $user): array
    {
        //==============================================================================
        // Collect Email Specific Tests Paramaters
        $emailParams = $emailClass::getFakeInstance($user)->getEmail()->getParams();

        //==============================================================================
        // Collect Email Common Paramaters
        $tmplParams = is_subclass_of($emailClass, HtmlTemplateProviderInterface::class)
            ? $emailClass::getTemplateParameters()
            : array();

        return array_replace_recursive($tmplParams, array(
            "params" => $emailParams
        ));
    }

    //==============================================================================
    // MJML TEMPLATES CONVERTER
    //==============================================================================

    /**
     * Get Setuped Mjml Convert
     *
     * @return null|MjmlConverter
     */
    public function getMjmlConverter(): ?MjmlConverter
    {
        //==============================================================================
        // Check Mjml API is Setup
        if (!$this->config->isMjmlAllowed()) {
            return $this->setError("Mjml Api is not configured");
        }

        //==============================================================================
        // Build Mjml Converter
        return new MjmlConverter(
            $this->config->getMjmlEndpoint(),
            $this->config->getMjmlAuth()
        );
    }

    /**
     * Convert Mjml to Html using API.
     *
     * @return null|string
     */
    public function convertMjmlToHtml(string $rawMjml): ?string
    {
        //==============================================================================
        // Get Mjml Converter
        $mjmlConverter = $this->getMjmlConverter();
        if (!$mjmlConverter) {
            return null;
        }
        //==============================================================================
        // CONVERT MJML TEMPLATES FROM API
        $rawHtml = $mjmlConverter->toHtml($rawMjml);
        if (!$rawHtml) {
            return $this->setError($mjmlConverter->getLastError());
        }

        return $rawHtml;
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
