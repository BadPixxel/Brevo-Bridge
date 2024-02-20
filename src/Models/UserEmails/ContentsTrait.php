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

namespace BadPixxel\BrevoBridge\Models\UserEmails;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Storage of Email Contents.
 */
trait ContentsTrait
{
    //==============================================================================
    // DATA STORAGE DEFINITION
    //==============================================================================

    /**
     * Target Email
     */
    #[ORM\Column(name: "email", type: Types::STRING, length: 255, nullable: false)]
    protected string $email;

    /**
     * Email Contents Checksum
     */
    #[ORM\Column(name: "md5", type: Types::STRING, length: 255, nullable: false)]
    protected string $md5;

    /**
     * Brevo Message ID
     */
    #[ORM\Column(name: "message_id", type: Types::STRING, length: 255, nullable: false)]
    protected string $messageId;

    /**
     * Brevo Message UUID
     */
    #[ORM\Column(name: "uuid", type: Types::STRING, length: 255, nullable: true)]
    protected ?string $uuid = null;

    /**
     * Message Subject
     */
    #[ORM\Column(name: "subject", type: Types::STRING, length: 512, nullable: false)]
    protected string $subject;

    /**
     * Email Raw Html Contents
     */
    #[ORM\Column(name: "html", type: Types::TEXT, nullable: true)]
    protected ?string $htmlContent = null;

    /**
     * Email Raw Text Contents
     */
    #[ORM\Column(name: "text", type: Types::TEXT, nullable: true)]
    protected ?string $textContent = null;

    /**
     * Email template ID
     */
    #[ORM\Column(name: "template_id", type: Types::INTEGER, nullable: true)]
    protected ?int $templateId = null;

    /**
     * Email template parameters
     */
    #[ORM\Column(name: "parameters", type: Types::ARRAY, nullable: true)]
    protected ?array $parameters = null;

    //==============================================================================
    // MAIN FUNCTIONS
    //==============================================================================

    /**
     * @param string      $uuid
     * @param null|string $htmlContent
     *
     * @return $this
     */
    public function setContents(string $uuid, ?string $htmlContent): static
    {
        $this->setUuid($uuid);
        $this->setHtmlContent($htmlContent);

        return $this;
    }

    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getMd5(): string
    {
        return $this->md5;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return null|string
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return null|string
     */
    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    /**
     * @return null|string
     */
    public function getTextContent(): ?string
    {
        return $this->textContent;
    }

    /**
     * @return null|int
     */
    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    /**
     * @return null|array
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param null|string $htmlContent
     *
     * @return $this
     */
    protected function setHtmlContent(?string $htmlContent): static
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    protected function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $md5
     *
     * @return $this
     */
    protected function setMd5(string $md5): static
    {
        $this->md5 = $md5;

        return $this;
    }

    /**
     * @param string $messageId
     *
     * @return $this
     */
    protected function setMessageId(string $messageId): static
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    protected function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    protected function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param null|string $textContent
     *
     * @return $this
     */
    protected function setTextContent(?string $textContent): static
    {
        $this->textContent = $textContent;

        return $this;
    }

    /**
     * @param null|int $templateId
     *
     * @return $this
     */
    protected function setTemplateId(?int $templateId): static
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * @param null|array $parameters
     *
     * @return $this
     */
    protected function setParameters(?array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
