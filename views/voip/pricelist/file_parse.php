<?php
use app\models\billing\Pricelist;
use app\models\billing\PricelistFile;
use app\classes\voip\BasePricelistLoader;

/** @var Pricelist $pricelist */
/** @var PricelistFile $file */
/** @var array $data */
/** @var array $settings */
/** @var array $loaders */
/** @var BasePricelistLoader $parser */

$overrideSettings = $parser->overrideSettings();

?>
<h2>
    <a href="/voip/pricelist/list?type=<?=$pricelist->type?>&orig=<?=$pricelist->orig?>&connectionPointId=<?=$model->connection_point_id?>">Прайслисты</a>
    -> <?=$pricelist->name?>
    -> Загрузка файла <?=$file->filename?>
</h2>

<?php

$columnTypes = [
    'prefix1'       => 'Префикс 1',
    'prefix2_smart' => 'Префикс 2 Умный',
    'prefix2_from'  => 'Префикс 2 От',
    'prefix2_to'    => 'Префикс 2 До',
    'rate'          => 'Цена',
    'destination'   => 'Направление',
    'comment'       => 'Комментарий',
];

$colCount = 0;
foreach ($data as $row) {
    if (count($row) > $colCount) {
        $colCount = count($row);
    }
}
?>

<form method="post">

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
                <label>Тип загрузки:
                    <select name="load_type" class="select2" <?=isset($overrideSettings['full']) ? 'readonly disabled' : ''?> >
                        <option value="full"     <?=isset($settings['full']) &&  $settings['full'] ? 'selected' : ''?> >Полная загрузка</option>
                        <option value="changes"  <?=isset($settings['full']) && !$settings['full'] ? 'selected' : ''?> >Изменения</option>
                    </select>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
            </td>
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