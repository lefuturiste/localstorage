<?php

class LocalStorageTest extends \PHPUnit\Framework\TestCase
{
    private $path = '../tmp/data.json';

    public function getInstance(): \Lefuturiste\LocalStorage\LocalStorage
    {
        return new \Lefuturiste\LocalStorage\LocalStorage($this->path);
    }

    public function testLocalStorage()
    {
        $localStorage = $this->getInstance();
        $localStorage->clear();
        $localStorage->persist();
        $localStorage = $this->getInstance();
        $this->assertEquals([], $localStorage->getAll());
        $localStorage->set('key', 'value');
        $this->assertNull($localStorage->get('foo'));
        $this->assertEquals('value', $localStorage->get('key'));
        $this->assertEquals(['key' => 'value'], $localStorage->getAll());
        $this->assertEquals(['key' => ['value' => 'value', 'at' => (new \Carbon\Carbon())->toDateTimeString()]], $localStorage->getState());
        $localStorage->save();
        $localStorage = $this->getInstance();
        $this->assertEquals(['key' => 'value'], $localStorage->getAll());
        $localStorage->clear();
        $localStorage->persist();
        $this->assertEquals([], $localStorage->getAll());
        $localStorage = $this->getInstance();
        $this->assertEquals([], $localStorage->getAll());
        $localStorage->set('foo', ['foo' => 'bar']);
        $localStorage->persist();
        $localStorage = $this->getInstance();
        $this->assertEquals(['foo' => 'bar'], $localStorage->get('foo'));
        $this->assertFalse($localStorage->isEmpty());
        $this->assertEquals(1, $localStorage->getCount());
        $localStorage->del('foo');
        $this->assertEquals([], $localStorage->getAll());
        $localStorage->write();
        $localStorage = $this->getInstance();
        $this->assertEquals([], $localStorage->getAll());
        $this->assertTrue($localStorage->isEmpty());
        $this->assertEquals(0, $localStorage->getCount());
    }

    public function testDeleteDatetime()
    {
        $localStorage = $this->getInstance();
        $localStorage->set('to_expire', 'foo');
        sleep(1);
        $localStorage->set('to_expire2', 'bar');
        sleep(1);
        $localStorage->deleteOlderThan(\Carbon\CarbonInterval::seconds(2));
        $this->assertNull($localStorage->get('to_expire'));
        $this->assertNotNull($localStorage->get('to_expire2'));
        $this->assertEquals('bar', $localStorage->get('to_expire2'));
    }
}
