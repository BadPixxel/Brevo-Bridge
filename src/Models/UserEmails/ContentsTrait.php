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

namespace BadPixxel\SendinblueBridge\Models\UserEmails;

use Doctrine\ORM\Mapping as ORM;

/**
 * Storage of Email Contents.
 *
 * @author nanard33
 */
trait ContentsTrait
{
    //==============================================================================
    // DATA STORAGE DEFINITION
    //==============================================================================

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="md5", type="string", length=255, nullable=false)
     */
    private $md5;

    /**
     * @var string
     *
     * @ORM\Column(name="message_id", type="string", length=255, nullable=false)
     */
    private $messageId;

    /**
     * @var null|string
     *
     * @ORM\Column(name="uuid", type="string", length=255, nullable=true)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=512, nullable=false)
     */
    private $subject;

    /**
     * @var null|string
     *
     * @ORM\Column(name="html", type="text", nullable=true)
     */
    private $htmlContent;

    /**
     * @var null|string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $textContent;

    /**
     * @var null|int
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    private $templateId;

    /**
     * @var null|array
     *
     * @ORM\Column(name="parameters", type="array", nullable=true)
     */
    private $parameters;

    //==============================================================================
    // MAIN FUNCTIONS
    //==============================================================================

    /**
     * @param string $htmlContent
     *
     * @return $this
     */
    public function setContents(string $uuid, ?string $htmlContent): self
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
     * @param string $htmlContent
     *
     * @return $this
     */
    protected function setHtmlContent(?string $htmlContent): self
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    protected function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $md5
     *
     * @return $this
     */
    protected function setMd5(string $md5): self
    {
        $this->md5 = $md5;

        return $this;
    }

    /**
     * @param string $messageId
     *
     * @return $this
     */
    protected function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    protected function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    protected function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $textContent
     *
     * @return $this
     */
    protected function setTextContent(?string $textContent): self
    {
        $this->textContent = $textContent;

        return $this;
    }

    /**
     * @param int $templateId
     *
     * @return $this
     */
    protected function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    protected function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
