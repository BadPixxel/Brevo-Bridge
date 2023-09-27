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

namespace BadPixxel\BrevoBridge\Helpers;

use Brevo\Client\Model\SendTransacSms;

/**
 * SendInBlue Sms Data Extractor.
 */
class SmsExtractor
{
    /**
     * Compute Sms Unique Discriminator.
     *
     * @param SendTransacSms $sms
     *
     * @return string
     */
    public static function md5(SendTransacSms $sms): string
    {
        return md5(serialize(self::getMd5Array($sms)));
    }

    /**
     * Build Send Sms Discriminator Data Array.
     *
     * @param SendTransacSms $sms
     *
     * @return array
     */
    private static function getMd5Array(SendTransacSms $sms): array
    {
        return array(
            'recipient' => $sms->getRecipient(),
            'contents' => $sms->getContent(),
        );
    }
}
