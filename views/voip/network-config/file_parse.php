<?php
use app\models\billing\NetworkFile;
use app\classes\voip\BaseNetworkLoader;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/** @var NetworkFile $file */
/** @var array $data */
/** @var array $settings */
/** @var array $loaders */
/** @var BaseNetworkLoader $parser */

$overrideSettings = $parser->overrideSettings();

echo Html::formLabel('Загрузка файла');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Местные префиксы', 'url' => Url::toRoute(['voip/network-config/list'])],
        ['label' => $networkConfig->name, 'url' => Url::toRoute(['voip/network-config/edit', 'id' => $networkConfig->id])],
        'Загрузка файла'
    ],
]);

$columnTypes = [
    'prefix1'       => 'Префикс 1',
    'prefix2_smart' => 'Префикс 2 Умный',
    'prefix2_from'  => 'Префикс 2 От',
    'prefix2_to'    => 'Префикс 2 До',
    'network_type'  => 'Код смежности',
];

$colCount = 0;
foreach ($data as $row) {
    if (count($row) > $colCount) {
        $colCount = count($row);
    }
}
?>

<form method="post">
    <input type="hidden" name="load_type" value="full"/>
    <table>
        <tr>
            <td>
                <label>Загрузчик:
                    <select name="loader" class="select2" style="width: 300px">
                        <?php foreach ($loaders as $loader => $name): ?>
                            <option value="<?=$loader?>"  <?=isset($settings['loader']) && $settings['loader'] == $loader ? 'selected' : ''?> ><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td>
                <button type="submit" name="btn_set_loader" class="btn btn-primary btn-sm">Установить загрузчик</button>
            </td>
        </tr>
        <tr>
            <td>
                <label>Префикс 0 <input name="prefix" value="<?=isset($settings['prefix']) ? $settings['prefix'] : '' ?>" <?=isset($overrideSettings['prefix']) ? 'readonly  disabled' : ''?> ></label>
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td>
                <label>Свернуть префиксы <input type="checkbox" name="compress" value="1" <?=isset($settings['compress']) && $settings['compress'] ? 'checked' : ''?> <?=isset($overrideSettings['compress']) ? 'readonly  disabled' : ''?> ></label>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label>Сохранить настройки <input type="checkbox" name="save_settings" value="1"  <?=isset($overrideSettings['save_settings']) ? 'readonly  disabled' : ''?> ></label>
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <label>Пропустить строк <input name="skip_rows" value="<?=isset($settings['skip_rows']) ? $settings['skip_rows'] : '0' ?>" <?=isset($overrideSettings['skip_rows']) ? 'readonly  disabled' : ''?> ></label>                &nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td>
                <label>Дата вступления в силу <input class="datepicker" name="effective_date" value="<?=(new \DateTime())->modify('+1 day')->format('Y-m-d')?>"></label>
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
            <td>
                <button type="submit" name="btn_upload" class="btn btn-primary btn-sm">Загрузить</button>
            </td>
        </tr>
    </table>
    <br/>

    <table class="table table-striped table-condensed table-bordered">
        <tr>
            <th>&nbsp;</th>
            <?php for($nCol = 1; $nCol <= $colCount; $nCol++): ?>
                <th><?=$nCol?></th>
            <?php endfor; ?>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <?php for($nCol = 1; $nCol <= $colCount; $nCol++): ?>
                <th>
                    <select name="col_<?=$nCol?>" <?=isset($overrideSettings['col_' . $nCol]) ? 'readonly disabled' : ''?> >
                        <option value="">-- // --</option>
                        <?php foreach ($columnTypes as $key => $name): ?>
                            <option value="<?=$key?>" <?=isset($settings['cols'][$key]) && $settings['cols'][$key] == $nCol ? 'selected' : '' ?> ><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                </th>
            <?php endfor; ?>
        </tr>
        <?php $nRow = 1; ?>
        <?php foreach ($data as $row): ?>
        <tr>
            <th><?=$nRow?></th>
            <?php $nCol = 1; ?>
            <?php foreach ($row as $cell): ?>
                <td><?=$cell?></td>
            <?php $nCol++; ?>
            <?php endforeach; ?>
        </tr>
        <?php $nRow++; ?>
        <?php endforeach; ?>
    </table>

</form>