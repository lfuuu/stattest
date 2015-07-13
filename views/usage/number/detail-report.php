<?php
use yii\helpers\Html;
?>
<h2>Детальный отчет по номерам</h2>

<br><br>

<form id="formVoipFreeStat" method=post>
    <table class="table table-bordered table-striped table-condensed">
        <tr>
            <td align="right">Город:</td>
            <td>
                <select name="cityId" class="select2" style="width: 200px" onchange="$('#formVoipFreeStat')[0].submit()">
                    <?php foreach($cityList as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $id == $cityId ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td align="right">Префикс:</td>
            <td><input name="prefix" class="form-control" value="<?= $prefix ?>"></td>

            <td colspan="2"></td>
        </tr>
        <tr>
            <td align="right">Группы DID:</td>
            <td>
                <?php foreach($didGroupList as $id => $name): ?>
                    <label><input type="checkbox" name="didGroups[]" value="<?= $id ?>" <?= in_array($id, $didGroups) ? 'checked' : '' ?> > <?= $name ?></label><br/>
                <?php endforeach; ?>
            <td align="right">Статусы:</td>
            <td>
                <?php foreach($statusList as $id => $name): ?>
                    <label><input type="checkbox" name="statuses[]" value="<?= $id ?>" <?= in_array($id, $statuses) ? 'checked' : '' ?> > <?= $name ?></label><br/>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td colspan="4" align="center">
                <input type=submit class="btn btn-primary" value="Сформировать" name="make">
            </td>
        </tr>
    </table>

    <hr>

    <br>

    <table class="table table-bordered table-striped table-condensed table-hover">
        <tr>
            <th>Номер</th>
            <th>Группа</th>
            <th>Состояние</th>
            <th>Клиент</th>
            <th><small>Среднее кол-во звонков в месяц,<br/> за последнее 3 месяца</small></th>
        </tr>
        <?php foreach($numbers as $n): ?>
            <tr>
                <td><?= Html::a($n['number'], '/usage/number/view?did=' . $n['number'], ['target'=>'_blank']) ?></td>
                <td><?= $didGroupList[$n['did_group_id']] ?></td>
                <td>
                    <?php if ($n['status'] == 'instock'): ?>
                        <span style="color: green; font-weight: bold">Свободен</span>
                    <?php elseif ($n['status'] == 'reserved'): ?>
                        <span style="color: #c40000; font-weight: bold">В резерве</span>
                        <?= $n['reserve_from'] ? 'с ' . substr($n['reserve_from'], 0, 10) : '' ?>
                        <?= $n['reserve_till'] ? 'по ' . substr($n['reserve_till'], 0, 10) : ''?>
                    <?php elseif ($n['status'] == 'active'): ?>
                        <span style="color: gray;">Используется</span>

                    <?php elseif ($n['status'] == 'hold'): ?>
                        <span style="color: blue;">В отстойнике</span>
                        <?= $n['hold_from'] ? 'с ' . substr($n['hold_from'], 0, 10) : '' ?>
                    <?php elseif ($n['status'] == 'notsell'): ?>
                        <span>Не продается</span>
                    <?php endif; ?>
                </td>
                <td><a href="./?module=clients&id=<?= $n['client_id'] ?>"><?= $n['client'] ?></a></td>
                <td <?php if (isset($n['count_avg3m']) && $n['count_avg3m'] > $minCalls): ?> style="font-weight: bold; color: red;"<?php endif; ?> >
                    <?php if (isset($n['count_avg3m'])): ?>
                        <?= $n['count_avg3m'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</form>
<hr>

Всего: <?= count($numbers) ?> номеров