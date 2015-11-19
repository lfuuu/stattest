<?php

use app\models\usages\UsageInterface;
use app\models\TechCpe;
use yii\helpers\Html;
use app\models\TariffVoip;
use app\models\Number;

$actual = function ($from, $to) {
    return (strtotime($from) < time() && strtotime($to) > time()) ? true : false;
};

$renderDate = function ($from, $to) {
    $max_possible_date = strtotime(UsageInterface::MAX_POSSIBLE_DATE);
    if(strtotime($to) >= $max_possible_date && strtotime($from) >= $max_possible_date){
        return 'Не был включен';
    }

    $res = $from;
    if(strtotime($to) < strtotime('2029-01-01'))
        $res  .= "&nbsp;-&nbsp;" . $to;
    return $res;
};


$ipstat = function ($data) {
    $v = $data;
    if (!$v)
        return '';
    $c = '';

    if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/", $v, $m))
        return '?';
    if (!isset($m[6]) || ($m[6] == 32)) {
        $R = array($ip = "{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}");
        if ($ip == "0.0.0.0")
            return "0.0.0.0";
    } else {
        $R = array("{$m[1]}.{$m[2]}.{$m[3]}." . ($m[4] + 1), "{$m[1]}.{$m[2]}.{$m[3]}." . ($m[4] + 2));
        $ip = "{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
    }

    return
        '<table cellspacing="0" cellpadding="0" border="0">' .
            '<tr>' .
                '<td valign=middle>' . TechCpe::dao()->getCpeIpStat($R) . '</td>' .
                '<td valign=middle' . $c . '>' .
                    '<a href="?module=monitoring&ip=' . $R[0] . '">' . $ip . '</a>' .
                    (isset($R[1]) ? '/<a href="?module=monitoring&ip=' . $R[1] . '">' . $m[6] . '</a>' : '') .
                '</td>' .
            '</tr>' .
        '</table>';
};

$has = false;
foreach ($services as $service) {
    if ($service) {
        $has = true;
        break;
    }
}
if ($has) :
    ?>
    <div class="service">
        <div class="row" style="padding-left: 15px;">
            <h2>Услуги</h2>
            <?php if ($services['ipport']) : ?>
                <div id="ipport">
                    <h3><a href="?module=services&action=in_view">Интернет подключения</a></h3>
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <tbody>
                        <?php foreach ($services['ipport'] as $service): ?>
                            <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                <td width="1%" nowrap="">
                                    <a href="/pop_services.php?table=usage_ip_ports&id=<?= $service->id ?>"
                                       target="_blank">
                                        <b><?= $service->id ?></b>
                                    </a>
                                    <a href="index.php?module=stats&action=internet">
                                        <img class="icon" src="/images/icons/stats.gif" alt="Статистика">
                                    </a>
                                    <?php if ($service->actual5d) : ?>
                                        <a href="/?module=services&action=in_act<?= $service->port->port_type == 'GPON' ? '_pon' : '' ?>&id=<?= $service->id ?>"
                                           target="_blank">
                                            <img class=icon src='/images/icons/act.gif' alt='Выписать акт'>
                                        </a>
                                        <a href="/?module=services&action=in_act<?= $service->port->port_type == 'GPON' ? '_pon' : '' ?>&id=<?= $service->id ?>&sendmail=1"
                                           target="_blank">
                                            <img class=icon src='/images/icons/act.gif' alt='Отправить акт по почте'>
                                        </a>
                                    <? endif; ?>
                                    <?php /*if ($actual($service->actual_from, $service->actual_to)): ?>
                                    <a href="/?module=services&action=in_close&id=<?= $service->id ?>">
                                        <img class=icon src='/images/icons/delete.gif' alt="Отключить">
                                    </a>
                                <?php endif; */ ?>
                                    <a href="index.php?module=tt&clients_client=<?= $service->client ?>&service=usage_ip_ports&service_id=<?= $service->id ?>&action=view_type&type_pk=1&show_add_form=true">
                                        <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                    </a>
                                </td>
                                <td><?= $service->address ?></td>
                                <td title="Время проверки скорости: <?= $service->speed_update ?>"
                                <?= ($service->tariff && $service->speed_mgts != $service->tariff->adsl_speed)
                                    ? 'style="color: #c40000;"><b>' . $service->speed_mgts . '</b> ' . $service->tariff->adsl_speed
                                    : ($service->tariff ? '>' . $service->tariff->adsl_speed : '>')
                                ?>
                                </td>
                                <td><?= $renderDate($service->actual_from, $service->actual_to); ?></td>
                                <td>
                                    <b>
                                        <?= $service->port->port_type ?>
                                        , <?= $service->port->port_name == 'mgts'
                                            ? $service->port->node
                                            : '<a href="/?module=routers&id=' . $service->port->node . '">' . $service->port->node . '</a>::' . $service->port->port_name ?>
                                    </b>
                                </td>
                                <td>
                                    <img alt="Текущий тариф" class="icon" src="/images/icons/tarif.gif">
                            <span style="color:#0000C0"
                                  title="Текущий тариф: <?= $service->tariff->mb_month ?>-<?= $service->tariff->pay_month ?>-<?= $service->tariff->pay_mb ?>">
                                <?= $service->tariff->name ?>
                                <i><?= $service->tariff->name ?></i>
                            </span>
                                </td>
                            </tr>

                            <?php
                            $j = true;
                            if ($service->cpeList) :
                                foreach ($service->cpeList as $cpe) :
                                    ?>
                                    <tr bgcolor="<?= $actual($cpe->actual_from, $cpe->actual_to) ? '#DCEEA9' : '#fffff5' ?>">
                                        <?php if ($j): ?>
                                            <td rowspan=<?= count($service->cpeList) ?> bgcolor=#DCEEA9>
                                                <a href='/?module=routers&action=d_add'>
                                                    <img class=icon src='/images/icons/add.gif'>
                                                </a>Создать устройство
                                            </td>
                                        <?php endif;
                                        $j = false; ?>
                                        <td align=left colspan=2>
                                            <a href="/?module=routers&action=d_edit&id=<?= $cpe->id ?>"><?= $cpe->model->vendor ?> <?= $cpe->model->model ?></a>
                                            <a href="/?module=routers&action=d_act&id=<?= $cpe->id ?>"><img
                                                    src="images/icons/act.gif"
                                                    class=icon></a>
                                            (<?= $cpe->model->type ?>, id=<?= $cpe->id ?>)
                                        </td>
                                        <td><?= $renderDate($cpe->actual_from, $cpe->actual_to); ?></td>
                                        <td><?= $cpe->ip ? $ipstat($cpe->ip) : 'ip не задан' ?><?= $cpe->ip_nat ? $ipstat($cpe->ip_nat) : '' ?></td>
                                        <td><?= $cpe->numbers ?></td>
                                    </TR>
                                <?php endforeach; ?>
                        <?php else : ?>
                            <tr bgcolor="#DCEEA9">
                                <td>
                                    <a href='/?module=routers&action=d_add'>
                                        <img class=icon src='/images/icons/add.gif'>
                                    </a>Создать устройство
                                </td>
                                <td align=left colspan=5>Клиентское устройство не определено</td>
                            </tr>
                        <?php endif; ?>
                            <?php
                            $j = true;
                            if ($service->netList) :
                                foreach ($service->netList as $net) :
                                    ?>
                                    <tr bgcolor="<?= $actual($net->actual_from, $net->actual_to) ? '#EEDCA9' : '#fffff5' ?>">
                                        <?php if ($j): ?>
                                            <td rowspan="<?= count($service->netList) ?>" bgcolor="<?= ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') ?>">
                                                <a href='/?module=services&action=in_add2&id=<?= $service->id ?>'>
                                                    <img class=icon src='/images/icons/add.gif'>
                                                </a>Создать сеть
                                            </td>
                                        <?php endif;
                                        $j = false; ?>
                                        <td colspan=2>
                                            <a href="/pop_services.php?table=usage_ip_routes&id=<?= $net->id ?>"
                                               target="_blank">
                                                <?= $net->net ?><?= $net->nat_net ? '<br>' . $net->nat_net : '' ?>
                                            </a> (id=<?= $net->id ?>)
                                        </td>
                                        <td><?= $renderDate($net->actual_from, $net->actual_to); ?></td>
                                        <td><?= $net->nat_net ? $ipstat($net->nat_net) : '' ?></td>
                                        <td><?= $net->comment ?></td>
                                    </TR>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr bgcolor="#fffff5">
                                    <td>
                                        <a href='/?module=services&action=in_add2&id=<?= $service->id ?>'>
                                            <img class=icon src='/images/icons/add.gif'>
                                        </a>Создать сеть
                                    </td>
                                    <td align=left colspan=5>Клиентские сети не определены</td>
                                </tr>
                            <?php endif; ?>
                            <tr style="border: 4px solid white; border-left: none; border-right: none;">
                                <td colspan="6" bgcolor="#000000" style="padding: 0; margin: 0; font-size: 1px;">&nbsp;</td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>


            <?php if ($services['voip'] || $services['voip_reserve']) : ?>
                <div id="voip">
                    <h3><a href="?module=services&action=vo_view">IP-телефония</a> <a
                            href="?module=services&action=vo_view">(актуальное состояние)</a></h3>
                    <a href="index.php?module=services&action=vo_act" target="_blank">
                        <img class="icon" src="/images/icons/act.gif">Выписать акт
                    </a>
                    <a href="index.php?module=services&action=vo_act&sendmail=1" target="_blank">
                        <img class="icon" src="/images/icons/act.gif">Отправить акт
                    </a>

                    <?php if (!empty($services['voip_reserve'])): ?>
                        <table class="table table-condensed">
                            <tbody>
                            <?php foreach ($services['voip_reserve'] as $number): ?>
                                <td>Зарезервирован</td>
                                <td><?= Html::a($number->number, '/usage/number/view?did=' . $number->number) ?></td>
                                <td>C <?= $number->reserve_from ?></td>
                                <td>По <?= $number->reserve_till ?></td>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($services['voip'])): ?>
                        <table class="table table-condensed">
                            <tbody>
                            <?php foreach ($services['voip'] as $service): ?>
                                <?php
                                /** @var TariffVoip $currentTariff */
                                /** @var \app\models\LogTarif $log */
                                $currentTariff = $log = null;
                                if (!($currentTariff = $service->tariff)) {
                                    $log = $service->getLogTariff($service->actual_from);
                                    $currentTariff = TariffVoip::findOne($log->id_tarif);
                                }
                                else {
                                    $log = $service->logTariff;
                                }
                                ?>
                                <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                    <td width="10%">
                                        <a href="/usage/voip/edit?id=<?= $service->id ?>"
                                           target="_blank"><?= $service->id ?></a>
                                        <a href="index.php?module=stats&action=voip&phone=<?= $service->region ?>_<?= $service->E164 ?>"
                                           style="float:right;">
                                            <img class="icon" src="/images/icons/stats.gif">
                                        </a>
                                        <a href="index.php?module=tt&clients_client=<?= $service['client'] ?>&service=usage_voip&service_id=<?= $service['id'] ?>&action=view_type&type_pk=1&show_add_form=true"
                                           style="float:right;">
                                            <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                        </a>
                                    </td>
                                    <td width="10%"><?= $service->regionName->name ?></td>
                                    <td style="font-size: 8pt;" width="15%">
                                        <a href="/usage/voip/edit?id=<?= $service->id ?>"
                                           target="_blank"><?= $service->address ?></a>
                                    </td>
                                    <td>
                                        <a href="/usage/voip/edit?id=<?= $service->id ?>" target="_blank">
                                            <?= $renderDate($service->actual_from, $service->actual_to); ?>
                                        </a>
                                    </td>
                                    <td><?= $service->E164 ?>&nbsp;x&nbsp;<?= $service->no_of_lines ?></td>
                                    <td style="font-size: 8pt;">
                                        <?= $currentTariff->name ?> (<?= $currentTariff->month_number . '-' . $currentTariff->month_line ?>)
                                        <?php
                                        if ($log->dest_group != '0') {
                                            echo '/ Набор:';
                                            if (strpos($log->dest_group, '5') !== false)
                                                echo ' Моб';
                                            if (strpos($log->dest_group, '1') !== false)
                                                echo ' МГ';
                                            if (strpos($log->dest_group, '2') !== false)
                                                echo ' МН';
                                            if (strpos($log->dest_group, '3') !== false)
                                                echo ' СНГ';
                                            echo $log->minpayment_group;
                                        }
                                        /** @var TariffVoip $tariff */
                                        $tariff = null;
                                        if (strpos($log->dest_group, '5') === false) {
                                            $tariff = TariffVoip::findOne($log->id_tarif_local_mob);
                                            echo '/ Моб ' . ($tariff ? $tariff->name : '') . ($log->minpayment_local_mob > 0 ? '(' . $log->minpayment_local_mob . ')' : '');
                                        }
                                        if (strpos($log->dest_group, '1') === false) {
                                            $tariff = TariffVoip::findOne($log->id_tarif_russia);
                                            echo '/ МГ ' . ($tariff ? $tariff->name : '') . ($log->minpayment_russia > 0) ? '(' . $log->minpayment_russia . ')' : '';
                                            $tariff = TariffVoip::findOne($log->id_tarif_russia_mob);
                                            echo '/ МГ ' . ($tariff ? $tariff->name : '');
                                        }
                                        if (strpos($log->dest_group, '2') === false) {
                                            $tariff = TariffVoip::findOne($log->id_tarif_intern);
                                            echo '/ МН ' . ($tariff ? $tariff->name : '') . ($log->minpayment_intern > 0 ? '(' . $log->minpayment_intern . ')' : '');
                                        }
                                        ?>
                                    </td>
                                    <td><?= (isset(Number::$statusList[$service->voipNumber->status]) ? Number::$statusList[$service->voipNumber->status] : $service->voipNumber->status); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($services['device']) : ?>
                <div id="device">
                    <h3><a href="/?module=routers&action=d_list">Клиентские устройства</a></h3>
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <thead>
                        <tr bgcolor="#FFFFD8">
                            <th width="5%">id</th>
                            <th width="15%">Устройство</th>
                            <th width="15%">Серийный номер</th>
                            <th width="20%">Дата-время</th>
                            <th width="20%">IP адрес / IP_nat</th>
                            <th width="15%">номера</th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($services['device'] as $device): ?>
                            <tr bgcolor="<?= $actual($device->actual_from, $device->actual_to) ? '#EEDCA9' : '#fffff5' ?>">
                                <td>
                                    <a href='/?module=routers&action=d_edit&id=<?= $device->id ?>'><?= $device->client ?></a>
                                    <a href='/?module=routers&action=d_act&id=<?= $device->id ?>' title='Бухгалтерский'><img
                                            src='/images/icons/act.gif' border=0></a>
                                    <a href='/?module=routers&action=d_act&act=2&id=<?= $device->id ?>'
                                       title='Технический'><img src='/images/icons/act.gif' border=0></a>
                                    <a href='/?module=routers&action=d_act&act=3&id=<?= $device->id ?>' title='Возврат'><img
                                            src='/images/icons/act.gif' border=0></a>
                                </td>
                                <td>
                                    <b><?= $device->model->vendor ?> <?= $device->model->model ?></b><br>MAC:<?= $device->mac ?>
                                </td>
                                <td><?= $device->serial ?></td>
                                <td><?= $renderDate($device->actual_from, $device->actual_to); ?></td>
                                <td><?= $ipstat($device->ip) ?><?= $device->ip_nat ? '&nbsp;/&nbsp;'.$ipstat($device->ip_nat) : '' ?></td>
                                <td><?= $device->numbers ?></td>
                                <td>
                                    <a href='/?module=routers&action=d_apply&dbform_action=delete&dbform[id]=<?= $device->id ?>'><img
                                            class=icon src='/images/icons/delete.gif'>удалить</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php
            foreach ($services['extra'] as $k => $service)
                if ($service->tariff->code == 'extra')
                    unset($services['extra'][$k]);
            if ($services['extra']):
                ?>
                <div id="extra">
                    <h3><a href="?module=services&action=ex_view">Дополнительные услуги</a></h3>
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <thead>
                        <tr bgcolor="#FFFFD8">
                            <th width="20%">Дата</th>
                            <th width="30%">Описание</th>
                            <th width="10%">Количество</th>
                            <th width="10%">Стоимость</th>
                            <th width="10%">Параметр</th>
                            <th width="20%">Период</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($services['extra'] as $service): ?>
                            <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                <td>
                                    <a href="/pop_services.php?table=usage_extra&id=<?= $service->id ?>" target="_blank">
                                        <?= $renderDate($service->actual_from, $service->actual_to); ?>
                                    </a>&nbsp;
                                    <a href="index.php?module=tt&clients_client=<?= $service->client ?>&service=usage_extra&service_id=<?= $service->id ?>&action=view_type&type_pk=1&show_add_form=true">
                                        <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                    </a>&nbsp;
                                    <?php /*if ($actual($service->actual_from, $service->actual_to)): ?>
                                    <a href="index.php?module=services&action=ex_close&id=<?= $service->id ?>">
                                        <img class="icon" src="/images/icons/delete.gif" alt="Отключить">
                                    </a>
                                <?php endif;*/ ?>
                                </td>
                                <td><?= $service->tariff->description ?></td>
                                <td><?= $service->amount ?></td>
                                <td><?= $service->tariff->price ?></td>
                                <td><?= $service->param_value ?></td>
                                <td><?= $service->tariff->period ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>


            <?php if ($services['welltime']): ?>
                <h3><a href="?module=services&action=welltime_view">WellTime</a></h3>
                <div id="welltime">
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <thead>
                        <tr bgcolor="#FFFFD8">
                            <th width="20%">Дата</th>
                            <th width="30%">Описание</th>
                            <th width="10%">Количество</th>
                            <th width="10%">Стоимость</th>
                            <th width="10%">IP</th>
                            <th width="20%">Роутер</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($services['welltime'] as $service): ?>
                            <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                <td><a href="/pop_services.php?table=usage_welltime&id=<?= $service->id ?>" target="_blank">
                                        <?= $renderDate($service->actual_from, $service->actual_to); ?>
                                    </a>&nbsp;
                                    <a href="index.php?module=tt&clients_client=<?= $service->client ?>&service=usage_welltime&service_id=<?= $service->id ?>&action=view_type&type_pk=3&show_add_form=true">
                                        <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                    </a>
                                </td>
                                <td><?= $service->tariff->description ?></td>
                                <td><?= $service->amount ?></td>
                                <td><?= $service->tariff->price ?></td>
                                <td><?= $service->ip ?></td>
                                <td><?= $service->router ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>


            <?php if ($services['virtpbx']): ?>
                <h3><a href="?module=services&action=virtpbx_view">Виртуальная АТС</a></h3>
                <div id="virtpbx">
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <thead>
                        <tr bgcolor="#FFFFD8">
                            <th width="20%">Дата</th>
                            <th width="30%">Описание</th>
                            <th width="10%">Стоимость</th>
                            <th width="40%">Регион</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($services['virtpbx'] as $service): ?>
                            <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                <td>
                                    <a href="/pop_services.php?table=usage_virtpbx&id=<?= $service->id ?>" target="_blank">
                                        <?= $renderDate($service->actual_from, $service->actual_to); ?>
                                    </a>&nbsp;
                                    <a href="index.php?module=stats&action=report_vpbx_stat_space&client_id=<?= $service->client ?>">
                                        <img class="icon" src="/images/icons/stats.gif">
                                    </a>
                                    <a href="index.php?module=tt&clients_client=<?= $service->client ?>&service=usage_virtpbx&service_id=<?= $service->id ?>&action=view_type&type_pk=3&show_add_form=true">
                                        <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                    </a>

                                </td>
                                <td><?= $service->tariff->description ?></td>
                                <td><?= $service->tariff->price ?></td>
                                <td><?= $service->regionName->name ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>


            <?php if ($services['sms']) : ?>
                <h3><a href="?module=services&action=sms_view">СМС</a></h3>
                <div id="sms">
                    <table cellspacing="4" cellpadding="2" width="100%" border="0">
                        <thead>
                        <tr bgcolor="#FFFFD8">
                            <th width="20%">Дата</th>
                            <th width="30%">Тариф</th>
                            <th width="50%">Стоимость, руб. с НДС</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($services['sms'] as $service): ?>
                            <tr bgcolor="<?= ($service->status == 'working') ? ($actual($service->actual_from, $service->actual_to) ? '#EEDCA9' : '#fffff5') : '#ffe0e0' ?>">
                                <td>
                                    <a href="/pop_services.php?table=usage_sms&id=<?= $service->id ?>" target="_blank">
                                        <?= $renderDate($service->actual_from, $service->actual_to); ?>
                                    </a>&nbsp;
                                    <a href="index.php?module=tt&clients_client=<?= $service->client ?>&service=usage_sms&service_id=<?= $service->id ?>&action=view_type&type_pk=3&show_add_form=true">
                                        <img class="icon" src="/images/icons/tt_new.gif" alt="Создать заявку">
                                    </a>
                                </td>
                                <td><?= $service->tariff->description ?></td>
                                <td><?= $service->tariff->per_month_price ?>
                                    / <?= $service->tariff->per_sms_price ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>
