<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel;

use Broadway\TestCase;

abstract class RepositoryTestCase extends TestCase
{
    protected $repository;

    public function setUp()
    {
        $this->repository = $this->createRepository();
    }

    abstract protected function createRepository();

    /**
     * @test
     */
    public function it_saves_and_finds_read_models_by_id()
    {
        $model = $this->createReadModel('1', 'othillo', 'bar');

        $this->repository->save($model);

        $this->assertEquals($model, $this->repository->find(1));
    }

    /**
     * @test
     */
    public function it_saves_and_finds_read_models_with_a_value_object_id()
    {
        $id     = new TestReadModelId('42');
        $model  = $this->createReadModel($id, 'othillo', 'bar');

        $this->repository->save($model);

        $this->assertEquals($model, $this->repository->find($id));
    }

    /**
     * @test
     */
    public function it_returns_null_if_not_found_on_empty_repo()
    {
        $this->assertEquals(null, $this->repository->find(2));
    }

    /**
     * @test
     */
    public function it_returns_null_if_not_found()
    {
        $model = $this->createReadModel('1', 'othillo', 'bar');

        $this->repository->save($model);

        $this->assertNull($this->repository->find(2));
    }

    /**
     * @test
     */
    public function it_finds_by_name()
    {
        $model1 = $this->createReadModel('1', 'othillo', 'bar');
        $model2 = $this->createReadModel('2', 'asm89', 'baz');

        $this->repository->save($model1);
        $this->repository->save($model2);

        $this->assertEquals([$model1], $this->repository->findBy(['name' => 'othillo']));
        $this->assertEquals([$model2], $this->repository->findBy(['name' => 'asm89']));
    }

    /**
     * @test
     */
    public function it_finds_by_one_element_in_array()
    {
        $model1 = $this->createReadModel('1', 'othillo', 'bar', ['elem1', 'elem2']);
        $model2 = $this->createReadModel('2', 'asm89', 'baz', ['elem3', 'elem4']);

        $this->repository->save($model1);
        $this->repository->save($model2);

        $this->assertEquals([$model1], $this->repository->findBy(['array' => 'elem1']));
        $this->assertEquals([$model2], $this->repository->findBy(['array' => 'elem4']));
    }

    /**
     * @test
     */
    public function it_finds_if_all_clauses_match()
    {
        $model1 = $this->createReadModel('1', 'othillo', 'bar');
        $model2 = $this->createReadModel('2', 'asm89', 'baz');

        $this->repository->save($model1);
        $this->repository->save($model2);

        $this->assertEquals([$model1], $this->repository->findBy(['name' => 'othillo', 'foo'=>'bar']));
        $this->assertEquals([$model2], $this->repository->findBy(['name' => 'asm89', 'foo'=>'baz']));
    }

    /**
     * @test
     */
    public function it_does_not_find_when_one_of_the_clauses_doesnt_match()
    {
        $model1 = $this->createReadModel('1', 'othillo', 'bar');
        $model2 = $this->createReadModel('2', 'asm89', 'baz');

        $this->repository->save($model1);
        $this->repository->save($model2);

        $this->assertEquals([], $this->repository->findBy(['name' => 'othillo', 'foo'=>'baz']));
        $this->assertEquals([], $this->repository->findBy(['name' => 'asm89', 'foo'=>'bar']));
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_found_nothing()
    {
        $model1 = $this->createReadModel('1', 'othillo', 'bar');
        $model2 = $this->createReadModel('2', 'asm89', 'baz');

        $this->repository->save($model1);
        $this->repository->save($model2);

        $this->assertEquals([], $this->repository->findBy(['name' => 'Jan']));
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_searching_for_empty_array()
    {
        $model = $this->createReadModel('1', 'othillo', 'bar');

        $this->repository->save($model);

        $this->assertEquals([], $this->repository->findBy([]));
    }

    /**
     * @test
     */
    public function it_removes_a_readmodel()
    {
        $model = $this->createReadModel('1', 'John', 'Foo', ['foo' => 'bar']);
        $this->repository->save($model);

        $this->repository->remove('1');

        $this->assertEquals([], $this->repository->findAll());
    }

    /**
     * @test
     */
    public function it_removes_a_read_model_using_a_value_object_as_its_id()
    {
        $id = new TestReadModelId('175');

        $model = $this->createReadModel($id, 'Bado', 'Foo', ['foo' => 'bar']);
        $this->repository->save($model);

        $this->repository->remove($id);

        $this->assertEquals([], $this->repository->findAll());
    }

    private function createReadModel($id, $name, $foo, array $array = [])
    {
        return new RepositoryTestReadModel($id, $name, $foo, $array);
    }
}

class TestReadModelId
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
