<?php
    use anmoroz\analytics\models\Condition;
    use yii\helpers\Html;
?>
<tr id="parametr_condition_<?= $conditionModel->parameter; ?>">
    <td>
        <?= Condition::getParameters($conditionModel->parameter); ?>
    </td>
    <td>
        <?= Html::dropDownList(
            'condition['.$conditionModel->parameter.'][condition]',
            $conditionModel->condition,
            Condition::getConditionForStringTypes(),
            ['class' => 'form-control input-small']
        ); ?>
    </td>
    <td>
        <?php
        if ($source) {
            if (is_callable($source)) {
                echo Html::dropDownList(
                    'condition['.$conditionModel->parameter.'][value]',
                    $conditionModel->value,
                    $source(),
                    ['class' => 'form-control']
                );
            } else if (is_array($source) && isset($source['ajaxUrl'])) {
                echo Html::dropDownList(
                    'condition['.$conditionModel->parameter.'][value]',
                    $conditionModel->value,
                    [],
                    ['class' => 'form-control select2-bind', 'data-url' => (string) $source['ajaxUrl']]
                );
            } else {
                echo Html::textInput(
                    'condition['.$conditionModel->parameter.'][value]',
                    $conditionModel->value,
                    ['class' => 'form-control']
                );
            }
        }
        ?>
    </td>
    <?= $this->render('_deleteButtonCell'); ?>
</tr>