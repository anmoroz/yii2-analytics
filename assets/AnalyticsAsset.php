<?php
/**
 * Author: Andrey Morozov
 * Date: 22.09.15
 */

namespace anmoroz\analytics\assets;

use yii\web\AssetBundle;

class AnalyticsAsset extends AssetBundle
{

    public $sourcePath = '@anmoroz/analytics/assets';

    public $js = [
        'analytics.js',
    ];

    public $css = [
        'analytics.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset'
    ];

    /**
     * Registers this asset bundle with a view.
     * @param \yii\web\View $view
     * @return static the registered asset bundle instance
     */
    public static function register($view)
    {
        $messages = [
            'condMsgError' => \Yii::t('analytics', 'Condition is already added'),
            'aggrMsgError' => \Yii::t('analytics', 'Aggregation is already added')
        ];

        $view->registerJs(
            'var errMessages = ' . \yii\helpers\Json::encode($messages) . ';' . PHP_EOL,
            \yii\web\View::POS_HEAD
        );
        return parent::register($view);
    }
}