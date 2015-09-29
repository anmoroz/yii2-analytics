
<?php
    use anmoroz\analytics\models\Condition;
    use yii\helpers\Html;
?>
<tr id="parametr_condition_<?= $conditionModel->parameter; ?>">
    <td><?= Condition::getParameters($conditionModel->parameter); ?></td>
    <td> → </td>
    <td>
        <?= Html::hiddenInput(
            'condition['.$conditionModel->parameter.'][condition]',
            Condition::CONDITION_EQ
        ); ?>
        <?= Html::dropDownList(
            'condition['.$conditionModel->parameter.'][value]',
            $conditionModel->value,
            Condition::getBooleanValues(),
            ['class' => 'form-control input-small']
        ); ?>
    </td>
    <?= $this->render('_deleteButtonCell'); ?>
</tr>
