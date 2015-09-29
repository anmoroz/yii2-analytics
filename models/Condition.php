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

class Condition extends Model
{
    use AnalyticsConfiguratorTrait;

    const VALUE_YES = 1;
    const VALUE_NO = 0;

    const CONDITION_EQ = '=';
    const CONDITION_NOT_EQ = '<>';
    const CONDITION_GTE = '>=';
    const CONDITION_GT = '>';
    const CONDITION_LTE = '<=';
    const CONDITION_LT = '<';
    const CONDITION_RANGE = 'range';

    /**
     * @var string Название атрибута в индексе
     */
    public $parameter;

    public $condition;
    public $value;

    public function rules()
    {
        return [
            [['parameter', 'condition', 'value'], 'required'],
            ['parameter', 'in', 'range' => array_keys(self::getParameters())],
            ['condition', 'validateCondition'],
            ['value', 'validateValue'],
            [['parameter', 'condition', 'value'], 'safe']
        ];
    }

    public static function getParameters($parameter = null)
    {
        /** @var \anmoroz\analytics\components\AbstractConfigurator $configurator */
        $configurator = self::getConfigurator();
        $fielsList = $configurator->getFieldsByBelongs(AbstractConfigurator::BELONGS_TO_CONDITION);
        $parameters = $configurator->extractValueByIndex($fielsList); // get index "name"
        return (!is_null($parameter) && isset($parameters[$parameter])) ? $parameters[$parameter] : $parameters;
    }

    public function getStringParameters()
    {
        $parameters = [];
        foreach ($this->getConfiguratorProperties() as $index => $property) {
            if (
                isset($property['type'])
                && (
                    $property['type'] === AbstractConfigurator::ES_TYPE_STRING
                    || $property['type'] === AbstractConfigurator::ES_TYPE_SHORT
                )
            ) {
                $parameters[] = $index;
            }
        }
        return $parameters;
    }

    public function getBooleanParameters()
    {
        $parameters = [];
        foreach ($this->getConfiguratorProperties() as $index => $property) {
            if (
                isset($property['type'])
                && $property['type'] === AbstractConfigurator::ES_TYPE_BOOLEAN
            ) {
                $parameters[] = $index;
            }
        }
        return $parameters;
    }

    public function getIntegerParameters()
    {
        $parameters = [];
        foreach ($this->getConfiguratorProperties() as $index => $property) {
            if (
                isset($property['type'])
                && $property['type'] === AbstractConfigurator::ES_TYPE_INTEGER
            ) {
                $parameters[] = $index;
            }
        }
        return $parameters;
    }

    private function getConfiguratorProperties()
    {
        return self::getConfigurator()->getProperties();
    }

    public function isStringParameter()
    {
        return in_array($this->parameter, $this->getStringParameters());
    }

    public function isIntegerParameter()
    {
        return in_array($this->parameter, $this->getIntegerParameters());
    }

    public function isBooleanParameter()
    {
        return in_array($this->parameter, $this->getBooleanParameters());
    }

    /**
     * Validation of the conditions in the query
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateCondition($attribute, $params)
    {
        $error = true;
        if ($this->isIntegerParameter() && in_array($this->$attribute, array_keys($this->getConditionForIntegerType()))) {
            $error = false;
        } elseif ($this->isBooleanParameter() && in_array($this->$attribute, array_keys($this->getConditionForBooleanTypes()))) {
            $error = false;
        } elseif ($this->isStringParameter() && in_array($this->$attribute, array_keys($this->getConditionForStringTypes()))) {
            $error = false;
        }

        if ($error) {
            $this->addError($attribute, Yii::t('analytics', 'Invalid condition in the query'));
        }
    }

    /**
     * @return \Elastica\Filter\AbstractFilter
     * @throws ErrorException
     */
    public function getElasticaFilter()
    {
        switch($this->condition) {
            case self::CONDITION_EQ:
            case self::CONDITION_NOT_EQ:
                $filter = new \Elastica\Filter\Term();
                $filter->addParam($this->parameter, $this->value);
                break;
            case self::CONDITION_GTE:
                $filter = new \Elastica\Filter\Range();
                $filter->addField($this->parameter, ['gte' => $this->value]);
                break;
            case self::CONDITION_GT:
                $filter = new \Elastica\Filter\Range();
                $filter->addField($this->parameter, ['gt' => $this->value]);
                break;
            case self::CONDITION_LTE:
                $filter = new \Elastica\Filter\Range();
                $filter->addField($this->parameter, ['lte' => $this->value]);
                break;
            case self::CONDITION_LT:
                $filter = new \Elastica\Filter\Range();
                $filter->addField($this->parameter, ['lt' => $this->value]);
                break;
            case self::CONDITION_RANGE:

                $filter = new \Elastica\Filter\Range($this->parameter, $this->getRangeArgs());
                break;
            default:
                throw new ErrorException('Unknown aggregation type');
        }
        return $filter;
    }

    /**
     * Validation atribute "value"
     *
     * @param string $attribute
     * @param array|null $params
     */
    public function validateValue($attribute, $params)
    {
        $error = true;
        if ($this->isBooleanParameter() && in_array($this->value, array_keys(self::getBooleanValues()))) {
            $error = false;
        } elseif (
            $this->isIntegerParameter()
            && (
                preg_match('/^\d+$/', $this->value)
                || ($this->condition == self::CONDITION_RANGE && preg_match('/^\d+:\d+$/', $this->value))
            )
        ) {
            $error = false;

            if (preg_match('/^\d+:\d+$/', $this->value)) {
                $args = $this->getRangeArgs();
                if ($args['gte'] > $args['lte']) {
                    $error = true;
                }
            }
        } elseif (
            $this->isStringParameter()
            && (
                preg_match('/^\d+$/', $this->value)
                || preg_match('/^(EC|RC)[\d]{6}$/u', $this->value)
            )
        ) {
            $error = false;
        }

        if ($error) {
            $this->addError($attribute, Yii::t('analytics', 'Incorrect value in the condition'));
        }
    }

    /**
     * @return array
     */
    private function getRangeArgs()
    {
        if (preg_match('/^\d+:\d+$/', $this->value)) {
            list($gte, $lte) = explode(':', $this->value);
        } else {
            $gte = $lte = $this->value;
        }
        return ['gte' => (int) $gte, 'lte' => (int) $lte];
    }

    public static function getBooleanValues($value = null)
    {
        $values = [
            self::VALUE_YES => Yii::t('analytics', 'yes'),
            self::VALUE_NO => Yii::t('analytics', 'no')
        ];
        return (!is_null($value) && isset($values[$value])) ? $values[$value] : $values;
    }

    /**
     * @param null|int $type
     * @return array
     */
    public static function getConditionForIntegerType($type = null)
    {
        $types = [
            self::CONDITION_EQ => Yii::t('analytics', 'equal'),
            self::CONDITION_NOT_EQ => Yii::t('analytics', 'not equal'),
            self::CONDITION_GTE => Yii::t('analytics', 'greater or equal'),
            self::CONDITION_GT => Yii::t('analytics', 'greater'),
            self::CONDITION_LTE => Yii::t('analytics', 'less or equal'),
            self::CONDITION_LT => Yii::t('analytics', 'less'),
            self::CONDITION_RANGE => Yii::t('analytics', 'range')
        ];
        return (!is_null($type) && isset($types[$type])) ? $types[$type] : $types;
    }

    /**
     * @param null|int $type
     * @return array
     */
    public static function getConditionForBooleanTypes($type = null)
    {
        $types = [
            self::CONDITION_EQ => Yii::t('analytics', 'equal')
        ];
        return (!is_null($type) && isset($types[$type])) ? $types[$type] : $types;
    }

    /**
     * @param null|int $type
     * @return array
     */
    public static function getConditionForStringTypes($type = null)
    {
        $types = [
            self::CONDITION_EQ => Yii::t('analytics', 'equal'),
            self::CONDITION_NOT_EQ => Yii::t('analytics', 'not equal')
        ];
        return (!is_null($type) && isset($types[$type])) ? $types[$type] : $types;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'parameter' => Yii::t('analytics', 'Parameter'),
            'condition' => Yii::t('analytics', 'Condition'),
            'value' => Yii::t('analytics', 'Value'),
        ];
    }
}