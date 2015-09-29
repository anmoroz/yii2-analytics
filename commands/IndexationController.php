<?php
/**
 * Author: Andrey Morozov
 * Date: 22.09.15
 */

namespace anmoroz\analytics\commands;

use yii\console\Controller;
use anmoroz\analytics\components\IndexationComponent;
use Yii;
use yii\helpers\Console;

class IndexationController extends Controller
{
    public $defaultAction = 'start';

    /**
     * Start ElasticSearch indexation process
     *
     * @param bool $dropIndex
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function actionStart($dropIndex = false)
    {
        if (
            $dropIndex
            && !$this->confirm("Do you wish to drop index?")
        ) {
            return self::EXIT_CODE_NORMAL;
        }

        $timeStart = microtime(true);
        $this->stdout('Start indexation process on ' . date("Y.m.d H:i:s", $timeStart) . PHP_EOL);

        /** @var \anmoroz\analytics\components\IndexationComponent $indexationComponent */
        $indexationComponent = Yii::createObject([
            'class'    => IndexationComponent::className(),
            'dropIndex' => (boolean) $dropIndex
        ]);

        $indexationComponent->run();

        $this->stdout('Stop indexation process on ' . date("Y.m.d H:i:s", $timeStart) . PHP_EOL);
        $this->stdout('Duration ' . gmdate("H:i:s", (microtime(true) - $timeStart)) . PHP_EOL);

        $totalAddedDocuments = $indexationComponent->getTotalAddedDocuments();

        $color = ($totalAddedDocuments > 0) ? Console::FG_GREEN : Console::FG_RED;
        $this->stdout('Done. Total added documents ' . $totalAddedDocuments . PHP_EOL, $color);

        return self::EXIT_CODE_NORMAL;
    }

}