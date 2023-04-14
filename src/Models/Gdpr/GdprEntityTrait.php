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

namespace BadPixxel\SendinblueBridge\Models\Gdpr;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Monolog\Logger;

/**
 * Implement Gdpr Actions for Emails & Sms Entities
 */
trait GdprEntityTrait
{
    /**
     * @inheritDoc
     */
    public static function configureGdprOutdatedQueryBuilder(QueryBuilder $queryBuilder, string $alias): void
    {
        //==============================================================================
        // Setup for OutDated Search
        $queryBuilder
            ->andWhere($queryBuilder->expr()->lt($alias.".sendAt", ":outDatedDate"))
            ->setParameter("outDatedDate", self::gdprOutdatedDate())
        ;
    }

    /**
     * @inheritDoc
     */
    public static function isGdprEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isGdprOutdated(Logger $logger): bool
    {
        //==============================================================================
        // Not Updated for a Long Time
        if ($this->getSendAt() < self::gdprOutdatedDate()) {
            return $logger->warning("Email | SMS is Too Old, to Delete!");
        }

        return false;
    }

    /**
     * Data Conservation Delay - Cleaned After 2 Years...
     *
     * @return DateTime
     */
    public static function gdprOutdatedDate(): DateTime
    {
        return new DateTime("-2 year");
    }
}
