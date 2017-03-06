<?php

require_once __DIR__ . '/../bootstrap.php';

class SerializeMe implements Broadway\Serializer\Serializable
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public static function deserialize(array $data)
    {
        return new SerializeMe($data['message']);
    }

    public function serialize()
    {
        return [
            'message' => $this->message
        ];
    }
}

// Setup the simple serializer
$serializer = new Broadway\Serializer\SimpleInterfaceSerializer();

// Create something to serialize
$serializeMe = new SerializeMe("Hi, i'm serialized?");

// Serialize
$serialized = $serializer->serialize($serializeMe);
var_dump($serialized);

// Deserialize
$deserialized = $serializer->deserialize($serialized);
var_dump($deserialized);
