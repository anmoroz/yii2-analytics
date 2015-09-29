<?php
// This is global bootstrap for autoloading

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../../../../vendor/autoload.php');
require(__DIR__ . '/../../../../vendor/yiisoft/yii2/Yii.php');

require(__DIR__ . '/unit/fixtures/AnalystsConfigurator.php');

$config = require(__DIR__ . '/../../../../config/console.php');

$application = new yii\console\Application(
    \yii\helpers\ArrayHelper::merge($config, [
        'modules' => [
            'analytics' => [
                'configClass' => 'fixtures\AnalystsConfigurator'
            ]
        ]
    ])
);
