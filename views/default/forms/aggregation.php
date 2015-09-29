<?php
    use anmoroz\analytics\models\Aggregation;
    use yii\helpers\Html;
?>
<tr id="parametr_aggregation_<?= $aggregationModel->parameter; ?>">
    <td>
        <?= Aggregation::getParameters($aggregationModel->parameter); ?>
    </td>
    <td>
        <?= Html::dropDownList(
            'aggregation['.$aggregationModel->parameter.'][type]',
            $aggregationModel->type,
            Aggregation::getTypes(),
            ['class' => 'form-control input-small']
        ); ?>
    </td>
    <td width="130">
        <?= Html::textInput(
            'aggregation['.$aggregationModel->parameter.'][additionalValue]',
            $aggregationModel->additionalValue,
            [
                'class' => 'form-control jsAggrAdditionalValue',
                'type' => 'number',
                'min' => 1,
                'max' => 100,
                'style' => 'display: none;'
            ]
        ); ?>
    </td>
    <?= $this->render('_deleteButtonCell'); ?>
</tr>