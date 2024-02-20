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
 * Trait for Access to Email Templating as Html
 */
trait HtmlTemplateTrait
{
    /**
     * Twig Template for SendInBlue Mjml Template.
     *
     * @var string
     */
    protected string $template = '@BrevoBridge/Layout/default.mjml.twig';

    /**
     * {@inheritdoc}
     */
    public function getTemplateHtml(): string
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTemplateParameters(): array
    {
        return array();
    }
}
