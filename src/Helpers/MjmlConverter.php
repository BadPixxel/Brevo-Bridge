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

use Symfony\Component\Cache\Simple\FilesystemCache;

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
     * @var FilesystemCache
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
        //==============================================================================
        // Check if Cache is Available
        if (class_exists(FilesystemCache::class, false)) {
            $this->cache = new FilesystemCache();
        }
    }

    /**
     * Convert Mjml to Html using API.
     *
     * @return null|string
     */
    public function toHtml(string $rawMjml): ?string
    {
        //==============================================================================
        // CONVERT MJML TEMPLATES FROM CACHE
        $cachedHtml = $this->fromCache($rawMjml);
        if ($cachedHtml) {
            return $cachedHtml;
        }
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
        // CURL REQUEST FAILLED
        if (!is_string($response) || curl_error($chr)) {
            return $this->setError(curl_error($chr));
        }
        //==============================================================================
        // DECODE RESPONSE
        $decoded = json_decode($response, true);
        if (null == $decoded) {
            return $this->setError("Unable to decode Mjml Response");
        }
        //==============================================================================
        // ERROR RESPONSE
        if (!isset($decoded['html'])) {
            return $this->setError(
                isset($decoded['message'])
                ? $decoded['message']
                : "An Error Occured during Mjml Parsing"
            );
        }
        curl_close($chr);
        //==============================================================================
        // STORE CONVERTED MJML TO CACHE
        $this->toCache($rawMjml, (string) $decoded['html']);

        return (string) $decoded['html'];
    }

    /**
     * Check if Cache is Avaiable for raw Mjml.
     *
     * @return null|string
     */
    private function fromCache(string $rawMjml): ?string
    {
        //==============================================================================
        // Check if Cache is Available
        if (!isset($this->cache)) {
            return null;
        }
        $cacheKey = self::getCacheKey($rawMjml);
        //==============================================================================
        // Check if Html is Already in Cache
        if (!$this->cache->has($cacheKey)) {
            return null;
        }

        return (string) $this->cache->get($cacheKey);
    }

    /**
     * Stroe Raw Html result in Cache
     *
     * @param string $rawMjml
     * @param string $rawHtml
     *
     * @return void
     */
    private function toCache(string $rawMjml, string $rawHtml): void
    {
        //==============================================================================
        // Check if Cache is Available
        if (!isset($this->cache)) {
            return;
        }

        $this->cache->set(self::getCacheKey($rawMjml), $rawHtml, 3600);
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
