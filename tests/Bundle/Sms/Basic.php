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

namespace BadPixxel\BrevoBridge\Tests\Bundle\Sms;

use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Tests\Bundle\Interfaces\DummySubjectAwareSmsInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Just a Basic Sms for Testing the API
 */
class Basic extends AbstractSms implements DummySubjectAwareSmsInterface
{
    /**
     * @var string
     */
    const TEST_SUBJECT = "No Subject is the Subject...";

    /**
     * @inheritdoc
     */
    public function configure(array $args): static
    {
        $this->setParameter("subject", $args["subject"]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        /** @var scalar $subject */
        $subject = $this->getParameters()["subject"] ?? null;

        return sprintf("This is a Demo SMS! %s", $subject);
    }

    /**
     * Create Email Instance in Demo Mode.
     */
    public function getFakeArguments(): array
    {
        return array(
            "subject" => self::TEST_SUBJECT,
        );
    }

    /**
     * @inheritdoc
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault("subject", self::TEST_SUBJECT)
        ;
    }
}
