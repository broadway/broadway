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

use Broadway\TestCase;

class MetadataTest extends TestCase
{
    /**
     * @test
     */
    public function it_contains_values_from_both_instances_after_merge()
    {
        $m1 = new Metadata(['foo' => 42]);
        $m2 = new Metadata(['bar' => 1337]);

        $expected = new Metadata(['foo' => 42, 'bar' => 1337]);
        $this->assertEquals($expected, $m1->merge($m2));
    }

    /**
     * @test
     */
    public function it_overrides_values_with_data_from_other_instance_on_merge()
    {
        $m1 = new Metadata(['foo' => 42]);
        $m2 = new Metadata(['foo' => 1337]);

        $expected = new Metadata(['foo' => 1337]);
        $this->assertEquals($expected, $m1->merge($m2));
    }

    /**
     * @test
     */
    public function it_constructs_an_instance_containing_the_key_and_value()
    {
        $m1 = Metadata::kv('foo', 42);

        $expected = new Metadata(['foo' => 42]);
        $this->assertEquals($expected, $m1);
    }
}
