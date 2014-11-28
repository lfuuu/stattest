<?php
use yii\helpers\Html;
/** @var $list \app\models\tariffication\ServiceType[] */
?>

<h2>Параметры:</h2>

<table class="table table-bordered table-hover table-condensed">
    <tr>
        <th>Название</th>
    </tr>
    <?php foreach($list as $item): ?>
    <tr>
        <td><?=Html::encode($item->name)?></td>
    </tr>
    <?php endforeach; ?>
</table>