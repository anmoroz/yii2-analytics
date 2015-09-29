<?php
/**
 * Author: Andrey Morozov
 * Date: 23.09.15
 */

/** @var \anmoroz\analytics\components\ViewHelperComponent $viewHelper */
$headers = $viewHelper->getHeaders($groupPosition);
?>

<table class="table table-condensed table-striped table-bordered">
    <thead>
    <?php foreach ($headers as $headerData) : ?>
        <tr class="info">
            <?php foreach ($headerData as $columnData) : ?>
                <th
                    <? if ($columnData['colspan'] > 1) echo 'colspan="'.$columnData['colspan'].'"'; ?>
                    <? if ($columnData['rowspan'] > 1) echo 'rowspan="'.$columnData['rowspan'].'"'; ?>
                    >
                    <?= $columnData['name']; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </thead>
    <tbody>
    <?php foreach($viewHelper->getRows($groupPosition) as $row) : ?>
        <tr>
            <?php if (isset($row['colspan'])) : ?>
                <td colspan="<?= $row['colspan']; ?>" class="warning"><?= $row['value']; ?></td>
            <?php else: ?>
                <?php foreach($row as $key => $col) : ?>
                    <td <?php if ($key == 0) echo 'class="info"'; ?>><?= $col; ?></td>
                <?php endforeach; ?>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
