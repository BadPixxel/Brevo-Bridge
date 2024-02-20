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

namespace BadPixxel\BrevoBridge\Tests\Bundle\Emails;

use BadPixxel\BrevoBridge\Interfaces\MjmlTemplateProviderInterface;
use BadPixxel\BrevoBridge\Models\Templating\MjmlTemplateTrait;
use BadPixxel\BrevoBridge\Tests\Bundle\Interfaces\DummyUrlsAwareInterface;

/**
 * Extends Basic Email for Building Template from Mjml
 */
class Template extends Basic implements MjmlTemplateProviderInterface, DummyUrlsAwareInterface
{
    use MjmlTemplateTrait;
}
