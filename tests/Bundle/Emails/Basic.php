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

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Tests\Bundle\Interfaces\DummyUrlsAwareInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Just a Basic Email
 */
class Basic extends AbstractEmail implements DummyUrlsAwareInterface
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
     * @inheritdoc
     */
    public function configure(array $args): static
    {
        $this->email->setTemplateId(1);
        $this->email->setSubject('[Brevo] '.self::TEST_SUBJECT);
        $this->email->setParams((object) array(
            'text' => $args['text'],
        ));

        return $this;
    }

    /**
     * Create Email Instance in Demo Mode.
     */
    public function getFakeArguments(): array
    {
        return array(
            "subject" => self::TEST_SUBJECT,
            "text" => self::TEST_MSG
        );
    }

    /**
     * @inheritdoc
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault("subject", self::TEST_SUBJECT)
            ->setDefault("text", self::TEST_MSG)
        ;
    }
}
