<?php
/**
 * Author: Andrey Morozov
 * Date: 24.09.15
 */

namespace anmoroz\analytics\components;

use anmoroz\analytics\models\Group;
use yii\base\ErrorException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class ViewHelperComponent extends Object
{
    const NULL_VALUE = '-';

    public $aggregationList;

    public $configuratorProperties;

    public $db;

    public $groupby;

    /**
     * @var \ArrayIterator
     */
    public $arrayIterator;

    private $cachedColumns = [];

    public function getHeaders($groupPosition)
    {
        if ($groupPosition == Group::GROUP_VERTICAL) {
            $header1 = [$this->getHeaderCellItem('', 1, 2)];
            $header2 = [];
            $columns = $this->getAllAggregationColumns();
            $assocAggregationList = ArrayHelper::index($this->aggregationList, 'index');

            foreach ($columns as $aggrIndex => $aggrFieldNames) {
                $header1[] = $this->getHeaderCellItem(
                    $assocAggregationList[$aggrIndex]['title'],
                    count($aggrFieldNames)
                );

                foreach ($aggrFieldNames as $fieldName) {
                    $header2[] = $this->getHeaderCellItem($fieldName);
                }
            }

            $headers = [$header1, $header2];

        } else {
            $header = [$this->getHeaderCellItem('Показатель')];
            foreach ($this->arrayIterator as $current) {
                $header[] = $this->getHeaderCellItem(
                    $this->getRealEntityName((int) $current['key'])
                );
            }
            $headers = [$header];
        }
        $this->arrayIterator->rewind();
        return $headers;
    }

    /**
     * @param int $id
     * @return mixed
     */
    private function getRealEntityName($id)
    {
        if (isset($this->configuratorProperties[$this->groupby]['entityNameSQL'])) {
            $sql = $this->configuratorProperties[$this->groupby]['entityNameSQL'];
            try {
                $realName = $this->db->createCommand($sql, [':id' => $id])->queryScalar();
                if ($realName) {
                    return $realName;
                }
            } catch (ErrorException $e) {

            }
        }
        return $id;
    }


    /**
     * @param string $name
     * @param int $colspan
     * @param int $rowspan
     * @param array $columns
     * @return array
     */
    private function getHeaderCellItem($name, $colspan = 1, $rowspan = 1, $columns = [])
    {
        return [
            'name' => $name,
            'colspan' => $colspan,
            'rowspan' => $rowspan,
            'columns' => $columns
        ];
    }

    /**
     * @param int $groupPosition
     * @return array
     */
    public function getRows($groupPosition)
    {
        $rows = [];
        $columns = $this->getAllAggregationColumns();
        if ($groupPosition == Group::GROUP_VERTICAL) {

            foreach ($this->arrayIterator as $current) {
                $cols = [ $this->getRealEntityName((int) $current['key']) ];

                foreach ($columns as $aggrIndex => $aggrFieldNames) {
                    $aggregationData = $this->extractDataFromAggregation($aggrIndex, $aggrFieldNames, $current);
                    foreach ($aggregationData as $value) {
                        array_push($cols, $value);
                    }
                }
                $rows[] = $cols;
            }
        } else {
            $assocAggregationList = ArrayHelper::index($this->aggregationList, 'index');
            foreach ($columns as $aggrIndex => $aggrFieldNames) {
                $rows[] = [
                    'colspan' => $this->arrayIterator->count() + 1,
                    'value' => $assocAggregationList[$aggrIndex]['title']
                ];

                foreach ($aggrFieldNames as $fieldName) {
                    $cols = [$fieldName];
                    foreach ($this->arrayIterator as $current) {
                        $cols[] = $this->findValue($current[$aggrIndex], $fieldName);
                    }
                    $rows[] = $cols;
                }

            }
        }

        $this->arrayIterator->rewind();

        return $rows;
    }

    /**
     * @param string $aggrIndex
     * @param array $aggrFieldNames
     * @param array $currentElementData Current iretator data
     * @return array
     */
    private function extractDataFromAggregation($aggrIndex, $aggrFieldNames, $currentElementData)
    {
        $data = [];
        foreach ($aggrFieldNames as $fieldName) {
            $aggrData = $currentElementData[$aggrIndex];
            $data[] = $this->findValue($aggrData, $fieldName);
        }
        return $data;
    }

    /**
     * @param array $aggrData
     * @param string $fieldName
     * @return float|string
     */
    private function findValue($aggrData, $fieldName)
    {
        $value = self::NULL_VALUE;
        if (isset($aggrData['buckets'])) {
            foreach ($aggrData['buckets'] as $bucketElement) {
                if ($bucketElement['key'] === $fieldName) {
                    $value = round($bucketElement['doc_count'], 2);
                    break;
                }
            }
        } else {
            $value = round($aggrData[$fieldName], 2);
        }
        return $value;
    }

    /**
     * @param string null|$aggKey
     * @return array
     */
    public function getAllAggregationColumns($aggKey = null)
    {
        if ($aggKey && isset($this->cachedColumns[$aggKey])) {
            return $this->cachedColumns[$aggKey];
        } elseif ($this->cachedColumns) {
            return $this->cachedColumns;
        }

        foreach (array_column($this->aggregationList, 'index') as $aggIndex) {
            $columns = [];
            $needSort = false;
            foreach ($this->arrayIterator as $current) {
                $aggrItem = $current[$aggIndex];

                if (isset($aggrItem['buckets'])) {
                    foreach ($aggrItem['buckets'] as $bucket) {
                        if (!in_array($bucket['key'], $columns)) {
                            $columns[] = $bucket['key'];
                            $needSort = true;
                        }
                    }
                } else {
                    $columns = array_keys($aggrItem);
                    break;
                }

            }
            $this->arrayIterator->rewind();
            if ($needSort) {
                sort($columns);
            }
            $this->cachedColumns[$aggIndex] = $columns;
        }

        return (!is_null($aggKey) && isset($this->cachedColumns[$aggKey])) ? $this->cachedColumns[$aggKey] : $this->cachedColumns;
    }
}