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
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\Managers\ErrorLoggerTrait;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\GetSmtpTemplateOverview;
use Brevo\Client\Model\UpdateSmtpTemplate;
use Exception;
use GuzzleHttp\Client;
use Twig\Environment;

/**
 * Emails Templates Manager for Brevo Api.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateManager
{
    use ErrorLoggerTrait;

    /**
     * Smtp API Service.
     *
     * @var null|TransactionalEmailsApi
     */
    protected ?TransactionalEmailsApi $smtpApi;

    public function __construct(
        private readonly Configuration $config,
        private readonly Environment    $twig
    ) {
    }

    //==============================================================================
    // EMAILS TEMPLATES MANAGER FUNCTIONS
    //==============================================================================

    /**
     * Compile Email Template to raw Html Contents.
     */
    public function compile(AbstractEmail $email): ?string
    {
        //==============================================================================
        // Compile Email From Mjml Twig Template
        if ($email instanceof MjmlTemplateProviderInterface) {
            try {
                //==============================================================================
                // Render Mjml from Twig Template
                $rawMjml = $this->twig->render(
                    $email->getTemplateMjml(),
                    $email->getTemplateParameters()
                );

                //==============================================================================
                // Convert Mjml to Html Template
                return $this->convertMjmlToHtml($rawMjml);
            } catch (\Throwable $exception) {
                return $this->setError(
                    sprintf("Mjml Compile Fails: %s", $exception->getMessage())
                );
            }
        }

        //==============================================================================
        // Compile Email From Html Twig Template
        if ($email instanceof HtmlTemplateProviderInterface) {
            try {
                //==============================================================================
                // Render Html from Twig Template
                return $this->twig->render(
                    $email->getTemplateHtml(),
                    $email->getTemplateParameters()
                );
            } catch (\Throwable $exception) {
                return $this->setError(
                    sprintf("Html Compile Fails: %s", $exception->getMessage())
                );
            }
        }

        return null;
    }

    /**
     * Update Email Html Template via Brevo API.
     */
    public function update(AbstractEmail $email): ?bool
    {
        //==============================================================================
        // Safety Checks
        if (!$templateId = $email->getEmail()->getTemplateId()) {
            return $this->setError("Email does not uses Html Templates");
        }
        //==============================================================================
        // Compile Email Raw Html
        if (!$rawHtml = $this->compile($email)) {
            return false;
        }

        try {
            //==============================================================================
            // Create Update Template Class
            $updateTmpl = new UpdateSmtpTemplate(array("htmlContent" => $rawHtml));
            //==============================================================================
            // Update the Email Template
            $this->getApi()->updateSmtpTemplate($templateId, $updateTmpl);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return true;
    }

    /**
     * Get Email Html Template via Brevo API.
     */
    public function get(AbstractEmail $email): ?GetSmtpTemplateOverview
    {
        //==============================================================================
        // Safety Checks
        if (!$templateId = $email->getEmail()->getTemplateId()) {
            return $this->setError("Email does not uses Html Templates");
        }

        try {
            //==============================================================================
            // Get the Email Template
            return $this->getApi()->getSmtpTemplate($templateId);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }
    }

    //==============================================================================
    // TEMPLATES PARAMETERS
    //==============================================================================

    /**
     * Build Parameters for Debug Email Display
     */
    public function getTmplParameters(AbstractEmail $email): array
    {
        return array(
            "templateId" => $email->getEmail()->getTemplateId(),
            "params" => (array) $email->getEmail()->getParams(),
            // Extra Data for Brevo Templates
            "contact" => array("EMAIL" => "sample@exemple.com"),
            "unsubscribe" => "unsubscribe.exemple.com"
        );
    }

    //==============================================================================
    // MJML TEMPLATES CONVERTER
    //==============================================================================

    /**
     * Get Mjml Convert
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
     * Access to Brevo API Service.
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
