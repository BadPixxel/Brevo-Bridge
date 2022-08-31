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

namespace BadPixxel\SendinblueBridge\Helpers;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Description of MjmlConverter
 *
 * @author BadPixxel <www.badpixxel.com>
 */
class MjmlConverter
{
    use \BadPixxel\SendinblueBridge\Models\Managers\ErrorLoggerTrait;

    /**
     * Mjml API App User
     *
     * @var string
     */
    private $appUser;

    /**
     * Mjml API Endpoint Url
     *
     * @var string
     */
    private $endpoint;

    /**
     * Simple cache
     *
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * Class constructor
     *
     * @param string $endpoint
     * @param string $appUser
     */
    public function __construct(string $endpoint, string $appUser)
    {
        $this->endpoint = $endpoint;
        $this->appUser = $appUser;
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Convert Mjml to Html using API.
     *
     * @param string $rawMjml
     *
     * @throws InvalidArgumentException
     *
     * @return null|string
     */
    public function toHtml(string $rawMjml): ?string
    {
        //==============================================================================
        // CONVERT MJML TEMPLATES FROM API OR CACHE
        $value = $this->cache->get(
            self::getCacheKey($rawMjml),
            function (ItemInterface $item) use ($rawMjml) {
                $item->expiresAfter(3600);

                return $this->toHtmlViaApi($rawMjml);
            }
        );

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * Convert Mjml to Html using API.
     *
     * @param string $rawMjml
     *
     * @return null|string
     */
    public function toHtmlViaApi(string $rawMjml): ?string
    {
        //==============================================================================
        // CONVERT MJML TEMPLATES FROM API
        $chr = curl_init($this->endpoint);
        if (!$chr) {
            return $this->setError("cUrl Init Failed");
        }

        curl_setopt($chr, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chr, CURLOPT_USERPWD, $this->appUser);
        curl_setopt($chr, CURLOPT_POST, 1);
        curl_setopt($chr, CURLOPT_POSTFIELDS, json_encode(array(
            "mjml" => $rawMjml
        )));
        $response = curl_exec($chr);
        //==============================================================================
        // CURL REQUEST FAILED
        if (!is_string($response) || curl_error($chr)) {
            return $this->setError(curl_error($chr));
        }
        //==============================================================================
        // DECODE RESPONSE
        /** @var null|array $decoded */
        $decoded = json_decode($response, true);
        if (null == $decoded) {
            return $this->setError("Unable to decode Mjml Response");
        }
        //==============================================================================
        // ERROR RESPONSE
        if (!isset($decoded['html'])) {
            return $this->setError(
                $decoded['message'] ?? "An Error Occurred during Mjml Parsing"
            );
        }
        curl_close($chr);

        return (string) $decoded['html'];
    }

    /**
     * Build Cache key
     *
     * @param string $rawMjml
     *
     * @return string
     */
    private static function getCacheKey(string $rawMjml): string
    {
        return md5(__CLASS__).".".md5($rawMjml);
    }
}
