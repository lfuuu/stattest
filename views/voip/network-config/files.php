<?php
use app\models\billing\Pricelist;
use app\models\billing\PricelistFile;
use app\models\billing\NetworkConfig;

/** @var NetworkConfig $networkConfig */
/** @var Pricelist $pricelist */
/** @var PricelistFile[] $files */
?>
<h2>
    <a href="/voip/network-config/list">Местные Префиксы</a>
    -> <?=$networkConfig->name?>
    -> Файлы
</h2>

<table align="right" width="100%">
    <tr>
        <td nowrap>
            <form action="/voip/network-config/file-upload?networkConfigId=<?=$networkConfig->id?>" method="post" enctype='multipart/form-data'>
                <input type="file" name="upfile" />
                <input type="submit" class="btn-primary" value="Загрузить файл" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
        <td nowrap>
            <form action="/voip/network-config/geo-upload?networkConfigId=<?=$networkConfig->id?>" method="post">
                <input type="submit" class="btn-primary" value="Загрузить из Россвязи" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </form>
        </td>
    </tr>
</table>

&nbsp;

<table class="table table-hover table-condensed table-striped">
    <tr>
        <th>Начало действия</th>
        <th>Имя файла</th>
        <th>&nbsp;</th>
        <th>Дата загрузки</th>
        <th>Количество строк</th>
    </tr>
    <?php foreach($files as $file): ?>
        <tr>
            <td <?=$file->active ? 'style="background-color: #9DEAAF"' : ''?> >
                <a href='index.php?module=voipnew&action=network_file_show&id=<?=$file->id?>'><?=$file->startdate?></a>
            </td>
            <td <?=$file->active ? 'style="background-color: #9DEAAF"' : ''?> >
                <a href='index.php?module=voipnew&action=network_file_show&id=<?=$file->id?>'><?=$file->file_name?></a>
            </td>
            <td>
                <?php if ($file->store_filename): ?>
                    <a href='/voip/network-config/file-download?fileId=<?=$file->id?>'>скачать</a>
                <?php endif; ?>
            </td>
            <td><?=$file->created_at?></td>
            <td><?=$file->rows?></td>
        </tr>
    <?php endforeach; ?>
</table>
