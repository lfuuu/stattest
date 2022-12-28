<?php

use app\classes\model\ActiveRecord;
use app\models\HistoryChanges;

/**
 * @var HistoryChanges[] $changes
 * @var ActiveRecord[] $models
 */
?>

<?php if (!$changes) : ?>
    Изменений не найдено
    <?php return ?>
<?php endif ?>

<?php
$prevModelId = null;
foreach ($changes as $k => $change):

    if ($prevModelId != $change->model_id) :

        if ($prevModelId) :
            ?></table><?php
        endif;

        ?>
        <table class="table table-condensed table-bordered" style="width: auto; margin-top: 20px">
            <tr class="info">
                <th>Что? Кто? Когда?</th>
                <th>Поле</th>
                <th nowrap>Старое значение</th>
                <th nowrap>Новое значение</th>
            </tr>
        <?php
    endif;

    $prevModelId = $change->model_id;

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
    <?php
    if (method_exists($models[$change->model], 'getHistoryHiddenFields') && $historyHiddenFields = $models[$change->model]::getHistoryHiddenFields($change->action)) {
        foreach ($historyHiddenFields as $historyHiddenField) {
            unset($data[$historyHiddenField]);
        }
    }

    foreach ($data as $field => $value):
        ?>
        <tr class="<?= $change->getColorClass() ?>">

            <?php if ($firstRow) : ?>
                <?php $firstRow = false; ?>
                <td nowrap rowspan="<?= count($data) ?>">

                    <?= $change->getActionName() ?>
                    <br/>

                    <?= $change->user ? $change->user->name : $change->user_id ?>
                    <br/>

                    <?= (new \app\classes\DateTimeWithUserTimezone($change->created_at))->getDateTime() ?>

                </td>
            <?php endif; ?>


            <td nowrap>
                <?= $models[$change->model]->getAttributeLabel($field) ?>
            </td>

            <td nowrap>
                <?php
                if ($oldData) {
                    $value = isset($oldData[$field]) ? $oldData[$field] : null;
                    if (method_exists($models[$change->model], 'prepareHistoryValue')) {
                        $value = $models[$change->model]::prepareHistoryValue($field, $value);
                    }

                    echo $value;
                }
                ?>
            </td>

            <td nowrap>
                <?php
                if ($newData) {
                    $value = isset($newData[$field]) ? $newData[$field] : null;
                    if (method_exists($models[$change->model], 'prepareHistoryValue')) {
                        $value = $models[$change->model]::prepareHistoryValue($field, $value);
                    }

                    echo $value;
                }
                ?>
            </td>

            <?php
            $firstRow = false;
            ?>
        </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
</table>