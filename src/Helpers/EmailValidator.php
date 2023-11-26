<?php

namespace BadPixxel\BrevoBridge\Helpers;

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Exception;

/**
 * Validate Brevo Smtp Email Configuration before Sending
 */
class EmailValidator
{
    /**
     * @throws Exception
     */
    public static function validate(AbstractEmail $email): array
    {
        //==============================================================================
        // Verify Sender
        /** @var null|SendSmtpEmailSender $sender */
        $sender = $email->getEmail()->getSender();
        if (empty($sender)) {
            throw new Exception("No sender defined");
        }
        //==============================================================================
        // Verify To
        if (empty($email->getEmail()->getTo())) {
            throw new Exception("No recipient defined");
        }
        //==============================================================================
        // Verify Subject
        if (empty($email->getEmail()->getSubject())) {
            throw new Exception("No subject defined");
        }
        //==============================================================================
        // Verify Parameters & Return Resolved
        return $email->getResolver()->resolve(
            (array) $email->getEmail()->getParams()
        );
    }
}