<?php
/**
 * Author: Andrey Morozov
 * Date: 23.09.15
 */

namespace anmoroz\analytics\components;

use anmoroz\analytics\models\Aggregation;
use anmoroz\analytics\models\Condition;
use anmoroz\analytics\models\Group;
use Elastica\Aggregation\Terms;
use Elastica\Filter\Bool;
use Elastica\Query;
use Yii;
use yii\base\ErrorException;

class ElasticaQueryBuilder
{

    const MAX_SIZE_AGGREGATION_ITEMS = 3000;

    const GROUP_AGGREGATION_NAME = 'groupby';

    /** @var array */
    private $errors = [];

    private $condition;
    private $group;
    private $aggregation;

    private $aggregationList;

    public function __construct(array $options)
    {
        $this->condition = $options['condition'];
        $this->group = $options['group'];
        $this->aggregation = $options['aggregation'];
    }

    /**
     * @return false|\Elastica\Query
     * @throws ErrorException
     */
    public function getQuery()
    {
        if ($this->validate() === false) {
            return false;
        }

        $termsAgg = new Terms(self::GROUP_AGGREGATION_NAME);
        $termsAgg->setField($this->group['by']);
        $termsAgg->setSize(self::MAX_SIZE_AGGREGATION_ITEMS);

        $filterBool = new Bool();

        if ($this->condition) {
            $this->addCondition($filterBool);
        }

        $this->addAggregation($termsAgg);

        $queryFiltred = new \Elastica\Query\Filtered();
        $queryFiltred->setFilter($filterBool);
        $queryFiltred->setQuery(new \Elastica\Query\MatchAll());

        $query = new Query();
        // These are not needed only aggregation
        $query->setSize(0);
        $query->setQuery($queryFiltred);
        $query->addAggregation($termsAgg);

        return $query;
    }

    /**
     * @param Terms $termsAgg
     * @throws ErrorException
     */
    private function addAggregation(Terms $termsAgg)
    {
        foreach ($this->aggregation as $parameter => $queryAttr) {
            $aggregationModel = new Aggregation();
            $aggregationModel->parameter = $parameter;
            $aggregationModel->attributes = $queryAttr;

            $itemAgg = $aggregationModel->getElasticaAggregations();
            $itemAgg->setField($parameter);

            $termsAgg->addAggregation($itemAgg);

            $this->aggregationList[] = [
                'type' => $aggregationModel->type,
                'index' => $aggregationModel->getAggregationName(),
                'title' => (string) $aggregationModel
            ];
        }
    }

    /**
     * @param Bool $filterBool
     * @throws ErrorException
     */
    private function addCondition(Bool $filterBool)
    {
        foreach ($this->condition as $parameter => $queryAttr) {
            $conditionModel = new Condition();
            $conditionModel->parameter = $parameter;
            $conditionModel->attributes = $queryAttr;

            $itemFilter = $conditionModel->getElasticaFilter();
            if ($conditionModel->condition !== Condition::CONDITION_NOT_EQ) {
                $filterBool->addMust($itemFilter);
            } else {
                $filterBool->addMustNot($itemFilter);
            }
        }
    }


    public function getAggregationList()
    {
        return $this->aggregationList;
    }

    /**
     * @return bool
     */
    public function validate()
    {

        if (empty($this->aggregation) || !is_array($this->aggregation)) {
            $this->errors[] =  Yii::t('analytics', 'Aggregation must add at least one parameter');
            return false;
        }
        if (empty($this->group) || !is_array($this->group)) {
            $this->errors[] =  Yii::t('analytics', 'Not found grouping');
            return false;
        }

        $valid = true;
        try {
            if ($this->condition) {
                foreach ($this->condition as $parameter => $attributes) {
                    $attributes['parameter'] = $parameter;
                    $valid = $this->validateModel(new Condition(), $attributes);
                }
            }

            foreach ($this->aggregation as $parameter => $attributes) {
                $attributes['parameter'] = $parameter;
                $valid = $this->validateModel(new Aggregation(), $attributes);
            }
            $valid = $this->validateModel(new Group(), $this->group);
        } catch (ErrorException $e) {
            $valid = false;
            $this->errors[] = Yii::t('analytics', 'Unknown error, please contact your administrator');
        }

        return $valid;
    }

    /**
     * @param \yii\base\Model $model
     * @param array $attributes
     * @return bool
     */
    private function validateModel(\yii\base\Model $model, array $attributes)
    {
        $model->attributes = $attributes;
        if ($model->validate() !== true) {
            $this->errors[] = $model->getFirstErrors();
            return false;
        }
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
