<?php

use yii\helpers\Html;
use anmoroz\analytics\models\Condition;
use anmoroz\analytics\models\Group;
use anmoroz\analytics\models\Aggregation;

/* @var $this yii\web\View */

\anmoroz\analytics\assets\AnalyticsAsset::register($this);
\anmoroz\analytics\assets\Select2Asset::register($this);
?>
<section class="content analytics">
    <h2><?= Yii::t('analytics', 'Analytics module'); ?></h2>

    <ul class="nav nav-tabs" id="analyticsTabs">
        <li class="active"><a data-toggle="tab" href="#query"><?= Yii::t('analytics', 'Settings'); ?></a></li>
        <li class="disabled"><a data-toggle="tab" href="#report"><?= Yii::t('analytics', 'Report'); ?></a></li>
    </ul>

    <div class="tab-content tab-product-content">
        <div id="query" class="tab-pane active padding">
            <div class="row">
                <div class="col-md-12">

                    <?= Html::beginForm(
                        \Yii::$app->urlManager->createUrl('analytics/default/report'),
                        'post',
                        ['class' => 'analytics-form']
                    ); ?>

                    <div class="alert alert-danger" role="alert"></div>

                    <h3><?= Yii::t('analytics', 'Conditions'); ?></h3>
                    <div class="panel panel-default analytycs-condition">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php $allParametrs = Condition::getParameters(); ?>
                                    <?php echo Html::dropDownList(
                                        'condition',
                                        '',
                                        $allParametrs,
                                        ['size' => count($allParametrs), 'id' => 'condition-select']
                                    ); ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= Html::button(
                                                Yii::t('analytics', 'Add'),
                                                ['class' => 'btn btn-sm btn-block btn-primary']
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div id="conditionList">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="pull-right">
                                                    <p class="text-muted">
                                                        <?= Yii::t('analytics', 'The ranges are defined by a colon, such as «4:5»'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table table-condensed table-striped"><tbody></tbody></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3><?= Yii::t('analytics', 'Grouping'); ?></h3>
                    <div class="analytycs-grouping">
                        <?= Html::dropDownList(
                            'group[by]',
                            '',
                            Group::getGroupByList(),
                            ['class' => 'form-control input-small']
                        ); ?><br/>
                        <?= Html::radioList(
                            'group[position]',
                            Group::GROUP_VERTICAL,
                            Group::getPositions()
                        ); ?>
                    </div>


                    <h3><?= Yii::t('analytics', 'Aggregations'); ?></h3>
                    <div class="panel panel-default analytycs-aggregation">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php $allAggregationParametrs = Aggregation::getParameters(); ?>
                                    <?= Html::dropDownList(
                                        'aggregation',
                                        '',
                                        $allAggregationParametrs,
                                        ['size' => count($allAggregationParametrs), 'id' => 'aggregation-select']
                                    ); ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= Html::button(
                                                Yii::t('analytics', 'Add'),
                                                ['class' => 'btn btn-sm btn-block btn-primary']
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div id="aggregationList">
                                        <table class="table table-condensed table-striped"><tbody></tbody></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="pull-left">
                                <?= Html::submitButton(
                                    Yii::t('analytics', 'Create a report'),
                                    ['class' => 'btn btn-danger']
                                ); ?>
                            </div>
                        </div>
                    </div>

                    <?= Html::endForm(); ?>
                </div>
            </div>
        </div>

        <div id="report" class="tab-pane padding">
            <div class="alert alert-info"><?= Yii::t('analytics', 'No data'); ?></div>
        </div>
    </div>
</section>