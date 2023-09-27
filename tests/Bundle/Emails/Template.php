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
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\Templating\MjmlTemplateTrait;
use BadPixxel\BrevoBridge\Tests\Bundle\Entity\User;

class Template extends AbstractEmail implements MjmlTemplateProviderInterface
{
    use MjmlTemplateTrait;

    /**
     * @var string
     */
    const TEST_SUBJECT = "No Subject is the Subject...";

    /**
     * @var string
     */
    const TEST_MSG = "This is a test Message!\n Please do not answers!";

    /**
     * SendInBlue Template ID.
     * [SITE][TESTS][CODES].
     *
     * @var int
     */
    protected static int $templateId = 1;

    /**
     * Twig Template for SendInBlue Mjml Template.
     *
     * @var string
     */
    protected static string $templateMjml = '@BrevoBridge/Layout/default.mjml.twig';

    /**
     * {@inheritdoc}
     */
    protected array $paramsDefaults = array(
        'subject' => null,
        'text' => null,
        'urls' => array(
            'home' => "https://www.brevo.com"
        ),
    );

    /**
     * {@inheritdoc}
     */
    protected array $paramsTypes = array(
        'urls' => 'array',
        'subject' => 'string',
        'text' => 'string',
    );

    /**
     * Construct Email.
     *
     * @param User|User[] $toUsers
     * @param string      $subject
     * @param string      $text
     */
    public function __construct($toUsers, string $subject, string $text)
    {
        parent::__construct($toUsers);

        $this->email->setTemplateId(self::$templateId);
        $this->email->setSubject('[Brevo] '.$subject);
        $this->email->setParams((object) array_replace_recursive(
            $this->paramsDefaults,
            array(
                'subject' => $subject,
                'text' => $text,
            )
        ));
    }

    /**
     * Create Fake Email Instance.
     *
     * @param User|User[] $toUsers
     *
     * @return self
     */
    public static function getFakeInstance($toUsers): self
    {
        return static::getDemoInstance($toUsers);
    }

    /**
     * Create Email Instance in Demo Mode.
     *
     * @param User|User[] $toUsers
     * @param string      $subject
     * @param string      $text
     *
     * @return self
     */
    protected static function getInstance($toUsers, string $subject, string $text): self
    {
        return new self($toUsers, $subject, $text);
    }

    /**
     * Create Email Instance in Demo Mode.
     *
     * @param User|User[] $toUsers
     *
     * @return self
     */
    protected static function getDemoInstance($toUsers): self
    {
        return self::getInstance($toUsers, self::TEST_SUBJECT, self::TEST_MSG);
    }
}
