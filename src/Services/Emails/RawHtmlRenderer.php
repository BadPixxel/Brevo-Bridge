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

namespace BadPixxel\BrevoBridge\Services\Emails;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Tooling Service for Rendering Raw Html Contents with Twig
 */
class RawHtmlRenderer
{
    /**
     * Constructor
     */
    public function __construct(
        private Environment $twig
    ) {
    }

    /**
     * Rendering Html Contents with Twig
     *
     * @throws Exception
     */
    public function render(string $rawHtml, array $parameters): Response
    {
        //==============================================================================
        // Render Raw Html with Parameter
        return new Response($this->twig->render("@BrevoBridge/Debug/email_view.html.twig", array(
            "rawHtml" => $rawHtml,
            'tmplParams' => $parameters,
        )));
    }

    /**
     * Rendering Raw Html Contents with Twig
     *
     * @throws Exception
     */
    public function renderRaw(string $rawHtml, array $parameters): string
    {
        //==============================================================================
        // Render Raw Html with Parameter
        return new Response($this->twig->render("@BrevoBridge/Debug/raw_view.html.twig", array(
            "rawHtml" => $this->convert($rawHtml),
            'tmplParams' => $parameters,
        )));
    }

    /**
     * Convert Brevo tags to Twig
     */
    public function convert(string $rawHtml): string
    {
        $rawHtml = str_replace("default:false", "default", $rawHtml);

        return str_replace("default:true", "default(true)", $rawHtml);
    }
}
