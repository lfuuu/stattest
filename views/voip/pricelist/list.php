<?php
use app\models\billing\Pricelist;
?>

<h2>
    <?php if ($type == Pricelist::TYPE_CLIENT && $orig == 1): ?>
        Прайлисты Клиентские Оригинация:
    <?php elseif ($type == Pricelist::TYPE_CLIENT && $orig == 0): ?>
        Прайлисты Клиентские Терминация:
    <?php elseif ($type == Pricelist::TYPE_OPERATOR && $orig == 1): ?>
        Прайлисты Операторские Оригинация:
    <?php elseif ($type == Pricelist::TYPE_OPERATOR && $orig == 0): ?>
        Прайлисты Операторские Терминация:
    <?php elseif ($type == Pricelist::TYPE_LOCAL && $orig == 0): ?>
        Прайлисты Местные Терминация:
    <?php endif; ?>
    <a href="/voip/pricelist/add?type=<?=$type?>&orig=<?=$orig?>&connectionPointId=<?=$connectionPointId?>">Добавить</a>
</h2>

<a href="/voip/pricelist/list?type=<?=$type?>&orig=<?=$orig?>&connectionPointId=0" class="btn btn-xs <?= $connectionPointId == 0 ? 'btn-primary':'btn-default'?>">Все</a>
<?php foreach ($connectionPoints as $srvId => $srvName): ?>
    <a href="/voip/pricelist/list?type=<?=$type?>&orig=<?=$orig?>&connectionPointId=<?=$srvId?>" class="btn btn-xs <?= $connectionPointId == $srvId ? 'btn-primary':'btn-default'?>"><?=$srvName?></a>
<?php endforeach; ?>
<br/><br/>

<table class="table table-condensed table-hover table-striped">
    <tr>
        <th>Точка присоединения</th>
        <th>Ид</th>
        <th>Прайслист</th>
        <?php if ($type == Pricelist::TYPE_OPERATOR && $orig == 0): ?>
            <th>Метод тарификации</th>
            <th>Инициация<br/>МГМН вызова</th>
            <th>Инициация<br/>зонового вызова</th>
        <?php endif; ?>
        <?php if ($type == Pricelist::TYPE_LOCAL): ?>
            <th>Местные префиксы</th>
        <?php endif; ?>
        <th>Цена</th>
        <th>Файлы</th>
        <th>Цены</th>
    </tr>
    <?php foreach ($pricelists as $pricelist): ?>
    <tr>
        <td><?= $connectionPoints[$pricelist->region] ?></td>
        <td><a href='/voip/pricelist/edit?id=<?=$pricelist->id?>'><?=$pricelist->id?></a></td>
        <td><a href='/voip/pricelist/edit?id=<?=$pricelist->id?>'><?=$pricelist->name?></a></td>
        <?php if ($type == Pricelist::TYPE_OPERATOR && $orig == 0): ?>
            <td>
                <?php if ($pricelist->tariffication_by_minutes): ?>
                    поминутная
                <?php else: ?>
                    посекундная
                    <?php if ($pricelist->tariffication_full_first_minute): ?>
                        со второй минуты
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            <td><?= $pricelist->initiate_mgmn_cost > 0 ? $pricelist->initiate_mgmn_cost : ''?></td>
            <td><?= $pricelist->initiate_zona_cost > 0 ? $pricelist->initiate_zona_cost : ''?></td>
        <?php endif; ?>
        <?php if ($type == Pricelist::TYPE_LOCAL): ?>
            <td><?=$networkConfigs[$pricelist->local_network_config_id]?></td>
        <?php endif; ?>
        <td><?=$pricelist->price_include_vat ? 'С НДС' : 'Без НДС'?></td>
        <td><a href='/voip/pricelist/files?pricelistId=<?=$pricelist->id?>'>файлы</a></td>
        <td><a href='index.php?module=voipnew&action=defs&pricelist=<?=$pricelist->id?>'>цены</a></td>
    </tr>
    <?php endforeach; ?>
</table>
