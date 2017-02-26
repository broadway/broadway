<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Domain;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Immutable DateTime implementation with some helper methods.
 */
class DateTime
{
    const FORMAT_STRING = 'Y-m-d\TH:i:s.uP';

    private $dateTime;

    private function __construct(DateTimeImmutable $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return DateTime
     */
    public static function now()
    {
        return new DateTime(
            DateTimeImmutable::createFromFormat(
                'U.u',
                sprintf('%.6F', microtime(true)),
                new DateTimeZone('UTC')
            )
        );
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->dateTime->format(self::FORMAT_STRING);
    }

    /**
     * @param string $dateTimeString
     *
     * @return DateTime
     */
    public static function fromString($dateTimeString)
    {
        return new DateTime(new DateTimeImmutable($dateTimeString));
    }

    /**
     * @return boolean
     */
    public function equals(DateTime $dateTime)
    {
        return $this->toString() === $dateTime->toString();
    }

    /**
     * @param DateTime $dateTime
     * @return bool
     */
    public function comesAfter(DateTime $dateTime)
    {
        return $this->dateTime > $dateTime->dateTime;
    }

    /**
     * @param string $intervalSpec
     *
     * @return DateTime
     */
    public function add($intervalSpec)
    {
        $dateTime = $this->dateTime->add(new DateInterval($intervalSpec));

        return new self($dateTime);
    }

    /**
     * @param string $intervalSpec
     *
     * @return DateTime
     */
    public function sub($intervalSpec)
    {
        $dateTime = $this->dateTime->sub(new DateInterval($intervalSpec));

        return new self($dateTime);
    }

    /**
     * @param DateTime $dateTime
     * @return DateInterval
     */
    public function diff(DateTime $dateTime)
    {
        return $this->dateTime->diff($dateTime->dateTime);
    }

    /**
     * @return DateTime
     */
    public function toBeginningOfWeek()
    {
        return new DateTime(new DateTimeImmutable($this->dateTime->format('o-\WW-1'), new DateTimeZone('UTC')));
    }

    /**
     * @return string
     */
    public function toYearWeekString()
    {
        return $this->dateTime->format('oW');
    }

    /**
     * @return DateTimeImmutable
     */
    public function toNative()
    {
        return $this->dateTime;
    }
}
