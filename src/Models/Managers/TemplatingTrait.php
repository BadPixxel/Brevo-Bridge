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

namespace BadPixxel\BrevoBridge\Models\Managers;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface as Router;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;
use Twig\Environment as Twig;

/**
 * Access to Symfony Templating Features.
 */
trait TemplatingTrait
{
    /**
     * Twig Service.
     *
     * @var Twig
     */
    private $twig;

    /**
     * Translator Service.
     *
     * @var Translator
     */
    private $translator;

    /**
     * Symfony Router.
     *
     * @var Router
     */
    private $router;

    /**
     * Get Translator.
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    /**
     * Generate Url.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    public function getUrl(string $route, array $parameters): string
    {
        return $this->router->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Render Contents of a Template.
     *
     * @param string $template
     * @param array  $parameters
     *
     * @return string
     */
    public function render(string $template, array $parameters): string
    {
        return $this->twig->render($template, $parameters);
    }

    /**
     * @param Twig       $twig
     * @param Translator $translator
     * @param Router     $router
     *
     * @return self
     */
    protected function setupTemplating(Twig $twig, Translator $translator, Router $router): self
    {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->router = $router;

        return $this;
    }
}
