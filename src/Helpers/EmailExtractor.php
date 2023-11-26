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

use Brevo\Client\Model\SendSmtpEmail;

/**
 * Brevo Emails Data Extractor.
 */
class EmailExtractor
{
    /**
     * Compute Email Unique Discriminator.
     *
     * @param SendSmtpEmail $email
     *
     * @return string
     */
    public static function md5(SendSmtpEmail $email): string
    {
        return md5(serialize(self::getMd5Array($email)));
    }

    /**
     * Build Send Email Discriminator Data Array.
     *
     * @param SendSmtpEmail $email
     *
     * @return array
     */
    private static function getMd5Array(SendSmtpEmail $email): array
    {
        return array(
            'subject' => $email->getSubject(),
            'html' => $email->getHtmlContent(),
            'text' => $email->getTextContent(),
            'template' => $email->getTemplateId(),
            'params' => $email->getParams(),
        );
    }
}
