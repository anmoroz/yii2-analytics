<?php
namespace components;

use anmoroz\analytics\components\ElasticaQueryBuilder;
use anmoroz\analytics\models\Aggregation;
use anmoroz\analytics\models\Group;

class ElasticaQueryBuilderTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {

    }

    protected function _after()
    {

    }

    private function options()
    {
        return [
            'condition' => [],
            'group' => [
                'by' => 'brandId',
                'position' => Group::GROUP_VERTICAL
            ],
            'aggregation' => [
                'totalNumberProperties' => [
                    'type' => Aggregation::TYPE_STATS,
                    'additionalValue' => ''
                ]
            ]
        ];
    }

    public function testValidate()
    {
        $builder = new ElasticaQueryBuilder($this->options());

        $this->assertTrue($builder->validate());

        $builder = new ElasticaQueryBuilder([
            'condition' => [],
            'group' => [],
            'aggregation' => []
        ]);
        $this->assertFalse($builder->validate());
    }

    public function testGetQuery()
    {
        $builder = new ElasticaQueryBuilder($this->options());
        $query = $builder->getQuery();

        $this->assertInstanceOf('Elastica\Query', $query);

        $queryAsArray = $query->toArray();

        $this->assertInternalType('array', $queryAsArray);
        $this->assertCount(3, $queryAsArray);
        $this->assertArrayHasKey('size', $queryAsArray);
        $this->assertArrayHasKey('query', $queryAsArray);
        $this->assertArrayHasKey('aggs', $queryAsArray);
    }
}