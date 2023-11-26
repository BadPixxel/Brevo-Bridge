<?php

namespace BadPixxel\BrevoBridge\Helpers;

use BadPixxel\BrevoBridge\Models\AbstractSms;
use Exception;

/**
 * Validate Brevo Transactional Sms Configuration before Sending
 */
class SmsValidator
{
    /**
     * @throws Exception
     */
    public static function validate(AbstractSms $sms): array
    {
        //==============================================================================
        // Verify Sender
        if (empty($sms->getSms()->getSender())) {
            throw new Exception("No sender defined");
        }
        //==============================================================================
        // Verify To
        if (empty($sms->getSms()->getRecipient())) {
            throw new Exception("No recipient defined");
        }
        //==============================================================================
        // Verify Parameters & Return Resolved
        return $sms->getResolver()->resolve($sms->getParameters());
    }
}