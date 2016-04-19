<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\EventStore;

use Broadway\Domain\DomainEventStream;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Version;
use Rhumsaa\Uuid\Uuid;

/**
 * @requires extension pdo_sqlite
 */
class BinaryDBALEventStoreTest extends DBALEventStoreTest
{
    const STREAM_TYPE = 'MyAggregate';

    /** @var \Doctrine\DBAL\Schema\Table  */
    protected $table;

    public function setUp()
    {
        if (Version::compare('2.5.0') >= 0) {
            $this->markTestSkipped('Binary type is only available for Doctrine >= v2.5');
        }

        $connection       = DriverManager::getConnection(array('driver' => 'pdo_sqlite', 'memory' => true));
        $schemaManager    = $connection->getSchemaManager();
        $schema           = $schemaManager->createSchema();
        $this->eventStore = new DBALEventStore($connection, new SimpleInterfaceSerializer(), new SimpleInterfaceSerializer(), 'events', true);

        $this->table = $this->eventStore->configureSchema($schema);

        $schemaManager->createTable($this->table);
    }

    /**
     * @test
     */
    public function table_should_contain_binary_uuid_column()
    {
        $uuidColumn = $this->table->getColumn('uuid');

        $this->assertEquals(16, $uuidColumn->getLength());
        $this->assertEquals(Type::getType(Type::BINARY), $uuidColumn->getType());
        $this->assertTrue($uuidColumn->getFixed());
    }

    /**
     * @test
     * @expectedException \Broadway\EventStore\Exception\InvalidIdentifierException
     * @expectedExceptionMessage Only valid UUIDs are allowed to by used with the binary storage mode.
     */
    public function it_throws_an_exception_when_an_id_is_no_uuid_in_binary_mode()
    {
        $id                = 'bleeh';
        $domainEventStream = new DomainEventStream(array(
            $this->createDomainMessage($id, 0),
        ));

        $this->eventStore->append(self::STREAM_TYPE, $id, $domainEventStream);
    }

    public function idDataProvider()
    {
        $uuid = Uuid::uuid4();

        return array(
            'UUID String' => array(
                $uuid->toString(), // test UUID
            ),
        );
    }
}
