<?php
/**
 * Author: Andrey Morozov
 * Date: 18.09.15
 */

namespace anmoroz\analytics\models;

use anmoroz\analytics\components\AbstractConfigurator;
use yii\base\ErrorException;
use yii\base\Model;
use Yii;
use anmoroz\analytics\traits\AnalyticsConfiguratorTrait;

class Aggregation extends Model
{
    use AnalyticsConfiguratorTrait;

    const TYPE_MIN = 'min';
    const TYPE_MAX = 'max';
    const TYPE_SUM = 'sum';
    const TYPE_AVG = 'avg';
    const TYPE_STATS = 'stats';
    const TYPE_VALUE_COUNT = 'value_count';
    const TYPE_TERMS = 'terms';
    const TYPE_HISTOGRAM = 'histogram';

    const AGGR_PREFIX = 'aggr_';

    public $parameter;
    public $type;
    public $additionalValue;

    public function rules()
    {
        return [
            [['parameter', 'type'], 'required'],
            ['parameter', 'in', 'range' => array_keys(self::getParameters())],
            [['parameter', 'type', 'additionalValue'], 'safe']
        ];
    }

    /**
     * @param string $aggType
     * @return array
     * @throws \yii\base\ErrorException
     */
    public static function getAggTemplate($aggType)
    {
        switch ($aggType) {
            case self::TYPE_MIN :
                $arrayTpl = ['value'];
                break;
            case self::TYPE_MAX:
                $arrayTpl = ['value'];
                break;
            case self::TYPE_SUM:
                $arrayTpl = ['value'];
                break;
            case self::TYPE_AVG:
                $arrayTpl = ['value'];
                break;
            case self::TYPE_STATS:
                $arrayTpl = ['count', 'min', 'max', 'avg', 'sum'];
                break;
            case self::TYPE_VALUE_COUNT:
                $arrayTpl = ['value'];
                break;
            case self::TYPE_TERMS:
                $arrayTpl = ['buckets'];
                break;
            case self::TYPE_HISTOGRAM:
                $arrayTpl = ['buckets'];
                break;
            default:
                throw new ErrorException('Unknown aggregation type');
        }
        return $arrayTpl;
    }

    public static function getTypes($type = null)
    {
        $types = [
            self::TYPE_MIN => Yii::t('analytics', 'Minimum value'),
            self::TYPE_MAX => Yii::t('analytics', 'Maximum value'),
            self::TYPE_SUM => Yii::t('analytics', 'The sum of'),
            self::TYPE_AVG => Yii::t('analytics', 'Average value'),
            self::TYPE_STATS => Yii::t('analytics', 'It includes both the minimum, maximum, average, sum'),
            self::TYPE_VALUE_COUNT => Yii::t('analytics', 'The number of values'),
            self::TYPE_TERMS => Yii::t('analytics', 'The number of unique values'),
            self::TYPE_HISTOGRAM => Yii::t('analytics', 'Histogram (interval)')
        ];
        return (!is_null($type) && isset($types[$type])) ? $types[$type] : $types;
    }

    /**
     * Возвращает название индекса, возвращаемого ES
     *
     * @return string
     */
    public function getAggregationName()
    {
        return self::AGGR_PREFIX . $this->type;
    }

    /**
     * @return \Elastica\Aggregation\AbstractSimpleAggregation
     * @throws \yii\base\ErrorException
     */
    public function getElasticaAggregations()
    {
        $aggregationName = $this->getAggregationName();
        switch($this->type) {
            case self::TYPE_MIN:
                $agg = new \Elastica\Aggregation\Min($aggregationName);
                break;
            case self::TYPE_MAX:
                $agg = new \Elastica\Aggregation\Max($aggregationName);
                break;
            case self::TYPE_SUM:
                $agg = new \Elastica\Aggregation\Sum($aggregationName);
                break;
            case self::TYPE_AVG:
                $agg = new \Elastica\Aggregation\Avg($aggregationName);
                break;
            case self::TYPE_STATS:
                $agg = new \Elastica\Aggregation\Stats($aggregationName);
                break;
            case self::TYPE_VALUE_COUNT:
                $agg = new \Elastica\Aggregation\ValueCount($aggregationName, $this->parameter);
                break;
            case self::TYPE_TERMS:
                $agg = new \Elastica\Aggregation\Terms($aggregationName);
                break;
            case self::TYPE_HISTOGRAM:
                $agg = new \Elastica\Aggregation\Histogram($aggregationName, $this->parameter, (int) $this->additionalValue);
                break;
            default:
                throw new ErrorException('Unknown aggregation type');
        }
        return $agg;
    }

    /**
     * Агрегация возможна только над числовыми параметрами
     *
     * @param null|int $parameter
     * @return array
     */
    public static function getParameters($parameter = null)
    {
        /** @var \anmoroz\analytics\components\AbstractConfigurator $configurator */
        $configurator = self::getConfigurator();
        $fielfsList = $configurator->getFieldsByBelongs(AbstractConfigurator::BELONGS_TO_AGGREGATION);
        $parameters = $configurator->extractValueByIndex($fielfsList);
        return (!is_null($parameter) && isset($parameters[$parameter])) ? $parameters[$parameter] : $parameters;
    }

    public function __toString()
    {
        $descr = $this->getParameters($this->parameter) . ' - ' . self::getTypes($this->type);
        if ((int) $this->additionalValue > 0) {
            $descr .= ' ' . Yii::t('analytics', 'value: {0}', (int) $this->additionalValue);
        }
        return $descr;
    }
}