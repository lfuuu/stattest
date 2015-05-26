

<h2>
    <?php if ($local == 0 && $orig == 0): ?>
    Прайлисты Терминация:
    <?php elseif ($local == 0 && $orig == 1): ?>
    Прайлисты Оригинация:
    <?php elseif ($local == 1 && $orig == 0): ?>
    Прайлисты Местные:
    <?php endif; ?>
    <a href="/voip/pricelist/add?local=<?=$local?>&orig=<?=$orig?>&connectionPointId=<?=$connectionPointId?>">Добавить</a>
</h2>

<a href="/voip/pricelist/list?local=<?=$local?>&orig=<?=$orig?>&connectionPointId=0" class="btn btn-xs <?= $connectionPointId == 0 ? 'btn-primary':'btn-default'?>">Все</a>
<?php foreach ($connectionPoints as $srvId => $srvName): ?>
    <a href="/voip/pricelist/list?local=<?=$local?>&orig=<?=$orig?>&connectionPointId=<?=$srvId?>" class="btn btn-xs <?= $connectionPointId == $srvId ? 'btn-primary':'btn-default'?>"><?=$srvName?></a>
<?php endforeach; ?>
<br/><br/>

<table class="table table-condensed table-hover table-striped">
    <tr>
        <th>Точка присоединения</th>
        <th>Ид</th>
        <th>Прайслист</th>
        <?php if ($local == 0 && $orig == 0): ?>
            <th>Метод тарификации</th>
            <th>Инициация<br/>МГМН вызова</th>
            <th>Инициация<br/>зонового вызова</th>
        <?php endif; ?>
        <?php if ($local == 1): ?>
            <th>Местные префиксы</th>
        <?php endif; ?>
        <th>Префиксы</th>
        <th>Цены</th>
    </tr>
    <?php foreach ($pricelists as $pricelist): ?>
    <tr>
        <td><?= $connectionPoints[$pricelist->region] ?></td>
        <td><a href='/voip/pricelist/edit?id=<?=$pricelist->id?>'><?=$pricelist->id?></a></td>
        <td><a href='/voip/pricelist/edit?id=<?=$pricelist->id?>'><?=$pricelist->name?></a></td>
        <?php if ($local == 0 && $orig == 0): ?>
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
        <?php if ($local == 1): ?>
            <td><?=$networkConfigs[$pricelist->local_network_config_id]?></td>
        <?php endif; ?>
        <td><a href='index.php?module=voipnew&action=raw_files&pricelist=<?=$pricelist->id?>'>префиксы</a></td>
        <td><a href='index.php?module=voipnew&action=defs&pricelist=<?=$pricelist->id?>'>цены</a></td>
    </tr>
    <?php endforeach; ?>
</table>