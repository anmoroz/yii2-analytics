<?php
/**
 * Author: Andrey Morozov
 * Date: 21.09.15
 */

namespace anmoroz\analytics\assets;

use yii\web\AssetBundle;

class Select2Asset extends AssetBundle
{

    public $sourcePath = '@bower/select2/dist';

    public $js = [
        'js/select2.min.js',
    ];

    public $css = [
        'css/select2.min.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset'
    ];
}