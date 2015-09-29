<?php
/**
 * Author: Andrey Morozov
 * Date: 22.09.15
 */

namespace anmoroz\analytics\traits;

trait AnalyticsConfiguratorTrait
{
    /**
     * @return \anmoroz\analytics\components\AbstractConfigurator
     */
    protected static function getConfigurator()
    {
        return \Yii::$app->getModule('analytics')->getServiceLocator()->get('configurator');
    }

    /**
     * @return \yii\db\Connection
     */
    protected static function getDBAdapter()
    {
        return \Yii::$app->getModule('analytics')->getServiceLocator()->get('db');
    }

    /**
     * @return \anmoroz\analytics\components\ElasticaBase
     */
    protected static function getElastica()
    {
        return \Yii::$app->getModule('analytics')->getServiceLocator()->get('elastica');
    }
}