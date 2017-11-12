<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Domain;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Immutable DateTime implementation with some helper methods.
 */
final class DateTime
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
    public static function now(): self
    {
        return new self(
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
    public function toString(): string
    {
        return $this->dateTime->format(self::FORMAT_STRING);
    }

    /**
     * @param string $dateTimeString
     *
     * @return DateTime
     */
    public static function fromString(string $dateTimeString): self
    {
        return new self(new DateTimeImmutable($dateTimeString));
    }

    /**
     * @return bool
     */
    public function equals(self $dateTime): bool
    {
        return $this->toString() === $dateTime->toString();
    }

    /**
     * @param DateTime $dateTime
     *
     * @return bool
     */
    public function comesAfter(self $dateTime): bool
    {
        return $this->dateTime > $dateTime->dateTime;
    }

    /**
     * @param string $intervalSpec
     *
     * @return DateTime
     */
    public function add(string $intervalSpec): self
    {
        $dateTime = $this->dateTime->add(new DateInterval($intervalSpec));

        return new self($dateTime);
    }

    /**
     * @param string $intervalSpec
     *
     * @return DateTime
     */
    public function sub(string $intervalSpec): self
    {
        $dateTime = $this->dateTime->sub(new DateInterval($intervalSpec));

        return new self($dateTime);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return DateInterval
     */
    public function diff(self $dateTime): DateInterval
    {
        return $this->dateTime->diff($dateTime->dateTime);
    }

    /**
     * @return DateTime
     */
    public function toBeginningOfWeek(): self
    {
        return new self(new DateTimeImmutable($this->dateTime->format('o-\WW-1'), new DateTimeZone('UTC')));
    }

    /**
     * @return string
     */
    public function toYearWeekString(): string
    {
        return $this->dateTime->format('oW');
    }

    /**
     * @return DateTimeImmutable
     */
    public function toNative(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
