<?php
/**
 * Author: Andrey Morozov
 * Date: 28.09.15
 */

namespace fixtures;

require(__DIR__ . '/../../../components/AbstractConfigurator.php');

use anmoroz\analytics\components\AbstractConfigurator;

class AnalystsConfigurator extends AbstractConfigurator
{
    public function getProperties()
    {
        return [
            'brandId' => [
                'type' => self::ES_TYPE_STRING,
                'belongs' => [self::BELONGS_TO_CONDITION, self::BELONGS_TO_GROUPING],
                'source' => function() {
                    return [1 => 'Sony', 2 => 'HP'];
                },
                'name' => 'Brand'
            ],
            'issetImage' => [
                'type' => self::ES_TYPE_BOOLEAN,
                'belongs' => [self::BELONGS_TO_CONDITION],
                'name' => 'The presence of the image'
            ],
            'totalNumberProperties' => [
                'type' => self::ES_TYPE_INTEGER,
                'belongs' => [self::BELONGS_TO_AGGREGATION],
                'name' => 'The total number of properties'
            ]
        ];
    }

    public function getCountSQL()
    {
        return '';
    }

    public function getIndexationSQL()
    {
        return '';
    }

    public function getTablePrimaryKey()
    {
        return '';
    }
}