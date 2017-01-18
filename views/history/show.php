<?php
use app\classes\model\HistoryActiveRecord;

/**
 * @var \app\models\HistoryChanges[] $changes
 * @var HistoryActiveRecord[] $models
 */
?>

<?php if (!$changes) : ?>
    Изменений не найдено
    <?php return ?>
<?php endif ?>

<table class="table table-condensed table-striped table-bordered" style="width: auto; margin-top: 20px">
    <tr class="info">
        <th>Дата</th>
        <th>Пользователь</th>
        <th>Поле</th>
        <th nowrap>Старое значение</th>
        <th nowrap>Новое значение</th>
    </tr>
    <?php foreach ($changes as $k => $change): ?>
        <?php
        $newData = json_decode($change->data_json, true);
        $oldData = json_decode($change->prev_data_json, true);

        /** @var array $data */
        if ($newData) {
            $data = $newData; // insert, update
        } elseif ($oldData) {
            $data = $oldData; // delete
        } else {
            continue;
        }

        $firstRow = true;
        ?>
        <?php foreach ($data as $field => $value): ?>
            <tr>
                <?php if ($firstRow) : ?>
                    <?php $firstRow = false; ?>
                    <td nowrap rowspan="<?= count($data) ?>">
                        <?php
                        if ($k == 0 || $change->created_at != $changes[$k - 1]->created_at) {
                            $date = new DateTime($change->created_at, new DateTimeZone('UTC'));
                            $date->setTimeZone(new DateTimeZone(Yii::$app->user->identity->timezone_name));
                            echo $date->format('d.m.Y H:i:s');
                        }
                        ?>
                    </td>

                    <td nowrap rowspan="<?= count($data) ?>">
                        <?= $change->user ? $change->user->name : $change->user_id ?>
                    </td>

                <?php endif; ?>


                <td nowrap>
                    <?= $models[$change->model]->getAttributeLabel($field) ?>
                </td>

                <td nowrap>
                    <?= method_exists($models[$change->model], 'prepareHistoryValue') ?
                        $models[$change->model]->prepareHistoryValue($field, $oldData[$field]) :
                        $oldData[$field] ?>
                </td>

                <td nowrap>
                    <?php $value = isset($newData[$field]) ? $newData[$field] : '' ?>
                    <?= method_exists($models[$change->model], 'prepareHistoryValue') ?
                        $models[$change->model]->prepareHistoryValue($field, $value) :
                        $value ?>
                </td>

                <?php
                $firstRow = false;
                ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>