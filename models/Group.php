<?php
/**
 * Author: Andrey Morozov
 * Date: 21.09.15
 */

namespace anmoroz\analytics\models;

use anmoroz\analytics\components\AbstractConfigurator;
use yii\base\Model;
use Yii;
use anmoroz\analytics\traits\AnalyticsConfiguratorTrait;

class Group extends Model
{
    use AnalyticsConfiguratorTrait;

    const GROUP_HORIZONTAL = 0;
    const GROUP_VERTICAL = 1;

    public $by;
    public $position;

    public function rules()
    {
        return [
            [['by', 'position'], 'required'],
            ['by', 'in', 'range' => array_keys(self::getGroupByList())],
            ['position', 'in', 'range' => array_keys(self::getPositions())],
            [['by', 'position'], 'safe']
        ];
    }

    public static function getPositions($position = null)
    {
        $positions = [
            self::GROUP_HORIZONTAL => Yii::t('analytics', 'Fields grouping positioned horizontally'),
            self::GROUP_VERTICAL => Yii::t('analytics', 'Fields grouping positioned vertically'),
        ];

        return (!is_null($position) && isset($positions[$position])) ? $positions[$position] : $positions;
    }

    public static function getGroupByList($groupBy = null)
    {
        /** @var \anmoroz\analytics\components\AbstractConfigurator $configurator */
        $configurator = self::getConfigurator();
        $fielfsList = $configurator->getFieldsByBelongs(AbstractConfigurator::BELONGS_TO_GROUPING);
        $groupingList = $configurator->extractValueByIndex($fielfsList);

        return (!is_null($groupBy) && isset($groupingList[$groupBy])) ? $groupingList[$groupBy] : $groupingList;
    }
}
