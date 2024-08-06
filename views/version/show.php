<?php
/**
 * @var HistoryVersion[] $versions
 * @var HistoryActiveRecord[] $models
 */

use app\classes\model\HistoryActiveRecord;
use app\models\HistoryVersion;

$links = [
    'app\models\ClientAccount' => '/account/edit?id=',
    'app\models\ClientContragent' => '/contragent/edit?id=',
    'app\models\ClientContragentPerson' => '/contragent/edit?id=',
    'app\models\ClientContract' => '/contract/edit?id=',
];
?>
<?php if (!$versions) : ?>
    Версий не найдено
    <?php return ?>
<?php endif ?>

<script src="views/version.js"></script>

<table class="table table-condensed table-striped table-bordered" style="width: auto; margin-top: 20px">
    <tr class="info">
        <th>Дата</th>
        <th>Последний изменивший Пользователь</th>
        <th>Атрибут</th>
        <th>Старое значение</th>
        <th>Новое значение</th>
    </tr>

    <?php $last = count($versions) - 1; ?>
    <?php foreach ($versions as $k => $version) : ?>
        <?php $firstRow = true;
        foreach ($version['diffs'] as $field => $values) : ?>
            <tr>

                <?php if ($firstRow) : ?>
                    <?php $firstRow = false; ?>

                    <td nowrap rowspan="<?= count($version['diffs']) ?>">
                        <?php if (isset($links[$version->model])) : ?>
                            <a href="<?= $links[$version->model] . $version->model_id . '&date=' . $version->date ?>"><?= $version->date ?></a>
                        <?php endif ?>
                        <?php if ($last !== $k || !$version['diffs']) : ?>
                            <i class="uncheck btn-delete-version" style="cursor: pointer;"
                               data-model="<?= $version->model ?>" data-model-id="<?= $version->model_id ?>"
                               data-date="<?= $version->date ?>"></i>
                        <?php endif; ?>
                    </td>

                    <td nowrap rowspan="<?= count($version['diffs']) ?>">
                        <?= ($version->user_id ? $version->user->name : '') ?>
                    </td>

                <?php endif; ?>

                <td>
                    <?= $models[$version->model]->getAttributeLabel($field) ?>
                </td>
                <td>
                    <?php
                    if (isset($values[0])) {
                        $value = $values[0];
                        if (method_exists($models[$version->model], 'prepareHistoryValue')) {
                            $value = $models[$version->model]::prepareHistoryValue($field, $value);
                        }

                        echo $value;
                    }
                    ?>
                </td>
                <td><?php
                    if (isset($values[1])) {
                        $value = $values[1];
                        if (method_exists($models[$version->model], 'prepareHistoryValue')) {
                            $value = $models[$version->model]::prepareHistoryValue($field, $value);
                        }

                        echo $value;
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>