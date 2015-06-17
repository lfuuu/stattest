<?php
use app\models\billing\Pricelist;
use app\models\billing\PricelistFile;

/** @var Pricelist $pricelist */
/** @var PricelistFile[] $files */
?>
<h2>
    <a href="/voip/pricelist/list?local=<?=$pricelist->local?>&orig=<?=$pricelist->orig?>&connectionPointId=<?=$model->connection_point_id?>">Прайслисты</a>
    -> <?=$pricelist->name?>
    -> Файлы
</h2>

<table align="right">
    <tr>
        <td nowrap>
            <form action="/voip/pricelist/file-upload?pricelistId=<?=$pricelist->id?>" method="post" enctype='multipart/form-data'>
                <input type="file" name="upfile" />
                <input type="submit" class="btn-primary" value="Загрузить файл" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
    </tr>
</table>

<table class="table table-hover table-condensed table-striped">
    <tr>
        <th>Начало действия</th>
        <th>Имя файла</th>
        <th>&nbsp;</th>
        <th>Тип</th>
        <th>Дата загрузки</th>
        <th>Количество строк</th>
    </tr>
    <?php foreach($files as $file): ?>
        <tr>
            <td <?=$file->active ? 'style="background-color: #9DEAAF"' : ''?> >
                <a href='index.php?module=voipnew&action=view_raw_file&id=<?=$file->id?>'><?=$file->startdate?></a>
            </td>
            <td <?=$file->active ? 'style="background-color: #9DEAAF"' : ''?> >
                <a href='index.php?module=voipnew&action=view_raw_file&id=<?=$file->id?>'><?=$file->filename?></a>
            </td>
            <td>
                <?php if ($file->store_filename): ?>
                    <a href='/voip/pricelist/file-download?fileId=<?=$file->id?>'>скачать</a>
                <?php endif; ?>
            </td>
            <td><?=$file->full ? 'Полный' : 'Изменения'?></td>
            <td><?=$file->date?></td>
            <td><?=$file->rows?></td>
        </tr>
    <?php endforeach; ?>
</table>
