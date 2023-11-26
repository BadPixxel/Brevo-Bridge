<?php

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
     * @inheritdoc
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault("subject", self::TEST_SUBJECT)
        ;
    }

    /**
     * @inheritDoc
     */
    function getContents(): string
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
}