<?php
    use anmoroz\analytics\models\Condition;
    use yii\helpers\Html;
?>
<tr id="parametr_condition_<?= $conditionModel->parameter; ?>">
    <td><?= Condition::getParameters($conditionModel->parameter); ?></td>
    <td>
        <?= Html::dropDownList(
            'condition['.$conditionModel->parameter.'][condition]',
            $conditionModel->condition,
            Condition::getConditionForIntegerType(),
            ['class' => 'form-control input-small']
        ); ?>
    </td>
    <td><?= Html::textInput(
            'condition['.$conditionModel->parameter.'][value]',
            $conditionModel->value,
            ['class' => 'form-control']
        ); ?></td>
    <?= $this->render('_deleteButtonCell'); ?>
</tr>
