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

namespace BadPixxel\BrevoBridge\Models\Templating;

/**
 * Trait for Access to Email Templating as Mjml
 */
trait MjmlTemplateTrait
{
    use HtmlTemplateTrait;

    /**
     * {@inheritdoc}
     */
    public function getTemplateMjml(): string
    {
        return $this->getTemplateHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateParameters(): array
    {
        return self::getTemplateCoreParameters();
    }

    /**
     * Get Templating Render Parameters
     *
     * @return array
     */
    private static function getTemplateCoreParameters(): array
    {
        return array(
            "mirror" => "#mirrorUrl",
            "unsubscribe" => "#unsubscribeUrl",
            "update_profile" => "#update_profileUrl",
            "contact" => array(
                "EMAIL" => "admin@exemple.com",
                "PHONE" => "06 06 06 06 06",
            ),
            "params" => array(),
        );
    }
}
