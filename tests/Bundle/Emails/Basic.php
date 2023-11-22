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

use BadPixxel\BrevoBridge\Interfaces\HtmlTemplateAwareInterface;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Tests\Bundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class Basic extends AbstractEmail implements HtmlTemplateAwareInterface
{
    /**
     * @var string
     */
    const TEST_SUBJECT = "No Subject is the Subject...";

    /**
     * @var string
     */
    const TEST_MSG = "This is a test Message!\n Please do not answers!";

    /**
     * Default Parameters.
     *
     * @var array
     */
    protected array $paramsDefaults = array(
        'subject' => null,
        'text' => null,
        'urls' => array(
            'home' => "https://www.brevo.com"
        ),
    );

    /**
     * Default Parameters Types.
     *
     * @var array
     */
    protected array $paramsTypes = array(
        'subject' => 'string',
        'text' => 'string',
        'urls' => 'array',
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

        $this->email->setSubject('[Brevo] '.$subject);
        $this->email->setParams((object) array(
            'subject' => $subject,
            'text' => $text,
            'urls' => array(
                'home' => "https://www.brevo.com"
            ),
        ));
    }

    /**
     * Create Email Instance in Demo Mode.
     *
     * @param User|User[] $toUsers
     *
     * @return self
     */
    public static function getDemoInstance(array|UserInterface $toUsers): self
    {
        return self::getInstance($toUsers, self::TEST_SUBJECT, self::TEST_MSG);
    }

    /**
     * @inheritDoc
     */
    public static function getTemplateId(): int
    {
        return 1;
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
    protected static function getInstance(array|User $toUsers, string $subject, string $text): self
    {
        return new self($toUsers, $subject, $text);
    }
}
