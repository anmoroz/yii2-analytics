<?php
/**
 * Author: Andrey Morozov
 * Date: 23.09.15
 */

namespace anmoroz\analytics\components;

use anmoroz\analytics\components\ElasticaQueryBuilder;

class ReportComponent extends \yii\base\Object
{
    /**
     * @var array
     */
    public $options;

    /**
     * @var \yii\di\ServiceLocator
     */
    public $locator;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * Number of items, filtered condition for
     * @var int
     */
    private $totalHits;

    /**
     * Search time query to index, sec.
     * @var int
     */
    private $totalTime;

    private $arrayIterator;


    public function init()
    {
        parent::init();

        $builder = new ElasticaQueryBuilder($this->options);
        /** @var \Elastica\Query $query */
        $query = $builder->getQuery();

        if (! $query) {
            $this->errors = $builder->getErrors();
            return;
        }

        /** @var \anmoroz\analytics\components\ElasticaBase $elasticaBase */
        $elasticaBase = $this->locator->get('elastica');
        $resultSet = $elasticaBase->getIndex()->search($query);
        $aggregations = $resultSet->getAggregation(ElasticaQueryBuilder::GROUP_AGGREGATION_NAME);

        $this->totalHits = $resultSet->getTotalHits();
        $this->totalTime = $resultSet->getTotalTime();

        $this->arrayIterator = new \ArrayIterator($aggregations['buckets']);

        $this->viewHelperToLocator($builder->getAggregationList());
    }

    /**
     * @return \ArrayIterator|null
     */
    public function getIterator()
    {
        return $this->arrayIterator;
    }

    /**
     * @return string
     */
    public function getErrorsAsString()
    {
        return implode('<br/>', $this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add ViewHelperComponent to service locator for view script
     *
     * @param array $aggregationList
     * @throws \yii\base\InvalidConfigException
     */
    private function viewHelperToLocator(array $aggregationList)
    {
        $this->locator->set(
            'viewHelper',
            \Yii::createObject([
                'class' => ViewHelperComponent::className(),
                'aggregationList' => $aggregationList,
                'arrayIterator' => $this->arrayIterator,
                'configuratorProperties' => $this->locator->get('configurator')->getProperties(),
                'db' => $this->locator->get('db'),
                'groupby' => $this->options['group']['by']
            ])
        );
    }
}