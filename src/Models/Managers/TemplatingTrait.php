<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Models\Managers;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Access to Symfony Templating Features.
 */
trait TemplatingTrait
{
    /**
     * Twig Service.
     *
     * @var EngineInterface
     */
    private $twig;

    /**
     * Translator Service.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Get Translator.
     *
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
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
     * @param UserManagerInterface $userManager
     *
     * @return self
     */
    protected function setupTemplating(EngineInterface $twig, TranslatorInterface $translator): self
    {
        $this->twig = $twig;
        $this->translator = $translator;

        return $this;
    }
}
