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

/**
 * Represents an important change in the domain.
 */
interface RepresentsDomainChange
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return int
     */
    public function getPlayhead();

    /**
     * @return Metadata
     */
    public function getMetadata();

    /**
     * @return mixed
     */
    public function getPayload();

    /**
     * @return DateTime
     */
    public function getRecordedOn();

    /**
     * @return string
     */
    public function getType();
}
