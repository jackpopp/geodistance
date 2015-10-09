<?php

require __DIR__.'/../../vendor/autoload.php';
use Jackpopp\GeoDistance\GeoDistanceTrait;
use Jackpopp\GeoDistance\QueryAdapters\QueryAdapter;
use Jackpopp\GeoDistance\QueryAdapters\pgsqlQueryAdapter;
use Illuminate\Container\Container;

class TestClass {
    use GeoDistanceTrait;

    public function getTable()
    {
        return 'test';
    }
}

class QueryAdapterTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $testClass;

    protected $queryAdapter;

    protected function _before()
    {
        $this->testClass = new TestClass;
        $this->queryAdapter = new QueryAdapter(null, null, null, null);
        $this->pgsqlQueryAdapter = new pgsqlQueryAdapter(null, null, null, null);
    }

    protected function _after()
    {
    }

    
    public function testAdapterReturnsQueryBuildObject()
    {
        //$container = new Container;
        //$this->tester->assertTrue($this->queryAdapter->outside() instanceof $class);
    }

    public function testUseQueryAdapterWhenConnectionIsMySQL()
    {
        $adapter = $this->testClass->resolveQueryAdapter('mysql');
        $this->tester->assertTrue($adapter instanceof $this->queryAdapter);
    }

    public function testUsePgsqlQueryAdapterWhenConnectionIsPostgresql()
    {
        $adapter = $this->testClass->resolveQueryAdapter('pgsql');
        $this->tester->assertTrue($adapter instanceof $this->pgsqlQueryAdapter);
    }

}