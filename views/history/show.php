<?php
use app\models\HistoryChanges;
use yii\db\ActiveRecord;

/** @var $model ActiveRecord */
/** @var $changes HistoryChanges[] */
?>
<table class="table table-condensed table-striped table-bordered" style="width: auto" align="center">
    <tr>
        <th>Пользователь</th>
        <th>Дата</th>
        <th>Поле</th>
        <th nowrap>Новое значение</th>
        <th nowrap>Старое значение</th>
    </tr>
    <?php foreach ($changes as $change): ?>
        <?php
        $date = new DateTime($change->created_at, new DateTimeZone('UTC'));
        $date->setTimeZone(new DateTimeZone(Yii::$app->user->identity->timezone_name));
        $newData = json_decode($change->data_json, true);
        $oldData = json_decode($change->prev_data_json, true);
        $rows = count($newData);
        $firstRow = true;
        ?>
        <?php foreach ($newData as $field => $value): ?>
            <tr>
                <?php if ($firstRow): ?>
                    <td nowrap rowspan="<?=$rows?>"><?= $change->user ? $change->user->name : $change->user_id ?></td>
                    <td nowrap rowspan="<?=$rows?>"><?= $date->format('d.m.Y H:i:s') ?></td>
                <?php endif; ?>
                <td nowrap><?= $model->getAttributeLabel($field) ?></td>
                <td nowrap><?= method_exists($model, 'prepareHistoryValue') ? $model->prepareHistoryValue($field, $value) : $value ?></td>
                <td nowrap><?= method_exists($model, 'prepareHistoryValue') ? $model->prepareHistoryValue($field, $oldData[$field]) : $oldData[$field] ?></td>
                <?php
                $firstRow = false;
                ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>