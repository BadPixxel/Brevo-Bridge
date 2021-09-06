<?php

/*
 *  Copyright (C) 2021 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Models\Templating;

use BadPixxel\SendinblueBridge\Services\SmtpManager;

/**
 * Trait for Access to Email Templating as Mjml
 */
trait MjmlTemplateTrait
{
    /**
     * {@inheritdoc}
     */
    public static function getTemplateId(): int
    {
        return static::$templateId;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTemplateHtml(): string
    {
        return self::getTemplateMjml();
    }

    /**
     * {@inheritdoc}
     */
    public static function getTemplateMjml(): string
    {
        return SmtpManager::getInstance()->render(
            static::$templateMjml,
            static::getTemplateParameters()
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getTemplateParameters(): array
    {
        return self::getTemplateCoreParameters();
    }

    /**
     * Get Templating Render Parameters
     *
     * @return array
     */
    public static function getTemplateCoreParameters(): array
    {
        return array(
            "mirror" => "#mirrorUrl",
            "unsubscribe" => "#unsubscribeUrl",
            "update_profile" => "#update_profileUrl",
            "contact" => array(
                "EMAIL" => "exemple@immo-pop.com",
                "PHONE" => "06 06 06 06 06",
            ),
            "params" => array(),
        );
    }
}
