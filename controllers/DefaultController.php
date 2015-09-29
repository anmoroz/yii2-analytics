<?php
/**
 * Author: Andrey Morozov
 * Date: 18.09.15
 */

namespace anmoroz\analytics\controllers;


use anmoroz\analytics\components\ReportComponent;
use anmoroz\analytics\models\Aggregation;
use anmoroz\analytics\models\Condition;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class DefaultController extends Controller
{

    public function behaviors()
    {
        return [
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'report'  => ['post'],
                    'add-aggregation'  => ['post'],
                    'add-condition'  => ['post']
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Show report table
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionReport()
    {
        $request = \Yii::$app->request;
        $options = [
            'condition' => $request->post('condition'),
            'group' => $request->post('group'),
            'aggregation' => $request->post('aggregation')
        ];

        $locator = $this->module->getServiceLocator();
        $report = Yii::createObject([
            'class'    => ReportComponent::className(),
            'options' => $options,
            'locator' => $locator
        ]);

        $arrayIterator = $report->getIterator();

        if ($report->getIterator()->count() == 0) {
            return $this->replyOk($this->renderPartial('notFound'));
        }

        if (! $arrayIterator) {
            return $this->replyError($report->getErrorsAsString());
        }

        $renderingResult = $this->renderPartial('report',[
            'viewHelper' => $locator->get('viewHelper'),
            'groupPosition' => $options['group']['position']
        ]);

        return $this->replyOk($renderingResult);
    }


    /**
     * Adding query aggregation
     *
     * @return array
     */
    public function actionAddAggregation()
    {
        $parameterId = \Yii::$app->request->post('parameterId');
        if (!$parameterId || !in_array($parameterId, array_keys(Aggregation::getParameters()))) {
            return $this->replyError(Yii::t('analytics', 'Parameter not found'));
        }

        $aggregationModel = new Aggregation();
        $aggregationModel->parameter = $parameterId;

        return $this->replyOk(
            $this->renderPartial('forms/aggregation', ['aggregationModel' => $aggregationModel])
        );
    }

    /**
     * Adding query conditions
     *
     * @return array
     */
    public function actionAddCondition()
    {
        $parameterId = \Yii::$app->request->post('parameterId');
        if (!$parameterId || !in_array($parameterId, array_keys(Condition::getParameters()))) {
            return $this->replyError(Yii::t('analytics', 'Parameter not found'));
        }

        $viewParams = $this->getViewAndParamsByAttr(['parameter' => $parameterId]);

        return $this->replyOk($this->renderPartial($viewParams['view'], $viewParams['data']));
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function getViewAndParamsByAttr($attributes)
    {
        $conditionModel = new Condition();
        $conditionModel->attributes = $attributes;
        if ($conditionModel->isIntegerParameter()) {
            $view = 'integerCondition';
        } elseif ($conditionModel->isBooleanParameter()) {
            $view = 'booleanCondition';
        } else {
            $view = 'stringCondition';
        }

        $property = $this->module->getProperties($attributes['parameter']);

        return [
            'view' => 'forms/' . $view,
            'data' => [
                'conditionModel' => $conditionModel,
                'source' => isset($property['source']) ? $property['source'] : null
            ]
        ];
    }

    private function replyError($data)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['res' => 'error', 'data' => $data];
    }


    private function replyOk($data)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['res' => 'ok', 'data' => $data];
    }
}