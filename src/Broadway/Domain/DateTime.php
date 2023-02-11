<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\Domain;

/**
 * Immutable DateTime implementation with some helper methods.
 */
final class DateTime
{
    public const FORMAT_STRING = 'Y-m-d\TH:i:s.uP';

    private $dateTime;

    private function __construct(\DateTimeImmutable $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public static function now(): self
    {
        return new self(
            \DateTimeImmutable::createFromFormat(
                'U.u',
                sprintf('%.6F', microtime(true)),
                new \DateTimeZone('UTC')
            )
        );
    }

    public function toString(): string
    {
        return $this->dateTime->format(self::FORMAT_STRING);
    }

    public static function fromString(string $dateTimeString): self
    {
        return new self(new \DateTimeImmutable($dateTimeString));
    }

    public function equals(self $dateTime): bool
    {
        return $this->toString() === $dateTime->toString();
    }

    public function comesAfter(self $dateTime): bool
    {
        return $this->dateTime > $dateTime->dateTime;
    }

    public function add(string $intervalSpec): self
    {
        $dateTime = $this->dateTime->add(new \DateInterval($intervalSpec));

        return new self($dateTime);
    }

    public function sub(string $intervalSpec): self
    {
        $dateTime = $this->dateTime->sub(new \DateInterval($intervalSpec));

        return new self($dateTime);
    }

    public function diff(self $dateTime): \DateInterval
    {
        return $this->dateTime->diff($dateTime->dateTime);
    }

    public function toBeginningOfWeek(): self
    {
        return new self(new \DateTimeImmutable($this->dateTime->format('o-\WW-1'), new \DateTimeZone('UTC')));
    }

    public function toYearWeekString(): string
    {
        return $this->dateTime->format('oW');
    }

    public function toNative(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
