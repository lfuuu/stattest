

<h2>
    Местные Префиксы:
    <a href="/voip/network-config/add">Добавить</a>
</h2>

<table class="table table-condensed table-hover table-striped">
    <tr>
        <th>Точка присоединения</th>
        <th>Ид</th>
        <th>Название</th>
        <th>Файлы</th>
    </tr>
    <?php foreach ($list as $item): ?>
    <tr>
        <td><?= $connectionPoints[$item->instance_id] ?></td>
        <td><a href='/voip/network-config/edit?id=<?=$item->id?>'><?=$item->id?></a></td>
        <td><a href='/voip/network-config/edit?id=<?=$item->id?>'><?=$item->name?></a></td>
        <td><a href="index.php?module=voipnew&action=network_config_show&id=<?=$item->id?>">Файлы</a></td>

    </tr>
    <?php endforeach; ?>
</table>