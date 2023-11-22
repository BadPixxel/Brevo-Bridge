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

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Tooling Service for Rendering Raw Html Contents with Twig
 */
class RawHtmlRenderer
{
    /**
     * @var string
     */
    const TMPL_DIR = "/var";

    /**
     * @var string
     */
    const TMPL_PATH = "/sib_email_template.html.twig";

    /**
     * Constructor
     */
    public function __construct(
        private KernelInterface $kernel,
        private Environment $twig
    ) {
    }

    /**
     * Rendering Raw Html Contents with Twig
     *
     * @throws Exception
     */
    public function render(string $rawHtml, array $parameters): Response
    {
        //==============================================================================
        // Push Raw Html as Twig Template
        $tmplPath = $this->kernel->getProjectDir().self::TMPL_DIR;
        file_put_contents($tmplPath.self::TMPL_PATH, $rawHtml);

        //==============================================================================
        // Add Temporary Path to Twig Loader
        /** @var FilesystemLoader $loader */
        $loader = $this->twig->getLoader();
        $loader->addPath($tmplPath);

        //==============================================================================
        // Render Raw Html with Parameter
        return new Response($this->twig->render("@BrevoBridge/Debug/email_view.html.twig", array(
            "tmplPath" => self::TMPL_PATH,
            'tmplParams' => $parameters,
        )));
    }
}
