<?php

namespace Broadway\Domain;

/**
 * @template M of object
 * @template P of mixed
 */
interface DomainMessageInterface
{
    public int $playhead { get; }
    /** @var SerializableMetaDataInterface<M>  */
    public SerializableMetaDataInterface $metadata { get; }
    /** @var P */
    public mixed $payload { get; }
}
