<?php
use app\classes\Html;
use app\models\VoipNumber;

echo Html::formLabel('Детальный отчет по номерам');
?>

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

    <?php if(count($numbers)): ?>
        <div style="float: right; padding-bottom: 5px;">
            <input type="submit" value="Получить список номеров" name="view-minimal" class="btn btn-primary" />
        </div>
        <br />
    <?php endif; ?>

    <table class="table table-bordered table-striped table-condensed table-hover">
        <tr>
            <th rowspan=2>Номер</th>
            <th rowspan=2>Группа</th>
            <th rowspan=2>Состояние</th>
            <th rowspan=2>Клиент</th>
            <th colspan=6><small>Кол-во звонков в месяц</small></th>
        </tr>
        <tr>
            <?php foreach($headMonths as $month): ?>
                <th><small><?=$month?><small></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach($numbers as $n): ?>
            <tr>
                <td><?= Html::a($n['number'], '/usage/number/view?did=' . $n['number'], ['target'=>'_blank']) ?></td>
                <td><?= $didGroupList[$n['did_group_id']] ?></td>
                <td>
                    <?php if ($n['status'] == 'instock'): ?>
                        <span style="color: green; font-weight: bold"><?= VoipNumber::NUMBER_STATUS_INSTOCK; ?></span>
                    <?php elseif ($n['status'] == 'reserved'): ?>
                        <span style="color: #c40000; font-weight: bold"><?= VoipNumber::NUMBER_STATUS_RESERVED; ?></span>
                        <?= $n['reserve_from'] ? 'с ' . substr($n['reserve_from'], 0, 10) : '' ?>
                        <?= $n['reserve_till'] ? 'по ' . substr($n['reserve_till'], 0, 10) : ''?>
                    <?php elseif ($n['status'] == 'active'): ?>
                        <span style="color: gray;"><?= VoipNumber::NUMBER_STATUS_ACTIVE; ?></span>

                    <?php elseif ($n['status'] == 'hold'): ?>
                        <span style="color: blue;"><?= VoipNumber::NUMBER_STATUS_HOLD; ?></span>
                        <?= $n['hold_from'] ? 'с ' . substr($n['hold_from'], 0, 10) : '' ?>
                    <?php elseif ($n['status'] == 'notsell'): ?>
                        <span><?= VoipNumber::NUMBER_STATUS_NOTSELL; ?></span>
                    <?php endif; ?>
                </td>
                <td><a href="/client/view?id=<?= $n['client_id'] ?>"><?= $n['client'] . ' ' . $n['company'] ?></a></td>
                <?php foreach($headMonths as $monthId => $month): 
                    $mCalls = $n["month"][$monthId];
                ?>
                    <td <?php if ($mCalls > $minCalls): ?> style="font-weight: bold; color: red;"<?php endif; ?> >
                        <?= ($mCalls > 0 ? $mCalls : "") ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</form>
<hr>

Всего: <?= count($numbers) ?> номеров
