<?php

/** @var \app\modules\uu\models\AccountTariff $accountTariff */

$isMobile = $accountTariff->service_type_id == \app\modules\uu\models\ServiceType::ID_VOIP && $accountTariff->number->ndc_type_id == \app\modules\nnp\models\NdcType::ID_MOBILE;

if (!$isMobile) {
    return '';
}

$nnpPackageStat = \app\models\billing\StatsAccount::getStatsNnpPackageMinute($accountTariff->client_account_id, $accountTariff->id, true);

if ($nnpPackageStat) {
    ?>
    <div class="well">
        <h2>Статистика по пакетам минут</h2>
        <div class="row">
            <div class="col-sm-5"><b>Название</b></div>
            <div class="col-sm-3"><b>Всего в пакете</b></div>
            <div class="col-sm-2"><b>Использовано</b></div>
            <div class="col-sm-2"><b>Остаток</b></div>
        </div>
        <?php

        foreach ($nnpPackageStat as $row) {
            ?>
            <div class="row"><?php
            ?>
            <div class="col-sm-5"><?= $row['name'] ?></div><?php
            ?>
            <div class="col-sm-3"><?= round($row['total_seconds'] / 60) ?> мин</div><?php
            ?>
            <div class="col-sm-2"><?= round($row['used_seconds'] / 60) ?> мин</div><?php
            ?>
            <div class="col-sm-2"><?= round(($row['total_seconds'] - $row['used_seconds']) / 60) ?> мин</div><?php
            ?></div><?php
        }
        ?>
    </div>
    <?php
}

$fSize = function ($size, $iInsMb = false) {
    if ($size < 1024) {
        return $size . 'b';
    } elseif ($size >= 1024 && $size < 1024 * 1024) {
        return round($size / 1024) . 'kb';
    } elseif ($size >= 1024 * 1024 && $size < 1024 * 1024 * 1024 || $iInsMb) {
        return round($size / 1024 / 1024) . 'Mb';
    } else {// ($size >= 1024*1024*1024 && $size < 1024*1024*1024*1024) {
        return round($size / 1024 / 1024 / 1024) . 'Gb';
    }
};

$internetStat = \app\models\billing\StatsAccount::getStatInternet($accountTariff->voip_number);

if ($internetStat) {

    $internetStat = array_filter($internetStat, function ($row) {
        return isset($row['bytes_consumed']) && isset($row['bytes_amount']);
    });

    ?>
    <div class="well">
        <h2>Статистика по пакетам Интернет</h2>
        <div class="row">
            <div class="col-sm-5"><b>Название</b></div>
            <div class="col-sm-3"><b>Всего в пакете</b></div>
            <div class="col-sm-2"><b>Использовано</b></div>
            <div class="col-sm-2"><b>Остаток</b></div>
        </div>
        <?php

        foreach ($internetStat as $row) {
            ?>
            <div class="row"><?php
            ?>
            <div class="col-sm-5"><?= ($tariff = \app\modules\uu\models\Tariff::findOne(['id' => $row['tariff_id']])) ? $tariff->name : $row['tariff_id'] . '*' ?></div><?php
            ?>
            <div class="col-sm-3"><?= $fSize($row['bytes_amount']) ?></div><?php
            ?>
            <div class="col-sm-2"><?= $fSize($row['bytes_consumed'], true) ?></div><?php
            ?>
            <div class="col-sm-2"><?= $fSize($row['bytes_amount'] - $row['bytes_consumed'], true) ?></div><?php
            ?></div><?php
        }
        ?>
    </div>
    <?php
}


$smsStat = \app\models\billing\StatsAccount::getStatSms2($accountTariff->voip_number);

if ($smsStat) {
    ?>
    <div class="well">
        <h2>Статистика по пакетам SMS</h2>
        <div class="row">
            <div class="col-sm-5"><b>Название</b></div>
            <div class="col-sm-2"><b>Всего</b></div>
            <div class="col-sm-2"><b>В&nbsp;пакете</b></div>
            <div class="col-sm-2"><b>Остаток</b></div>
        </div>
        <?php

        foreach ($smsStat as $row) {
            ?>
            <div class="row"><?php
            ?>
            <div class="col-sm-5"><?= $row['tariff_name'] ?></div><?php
            ?>
            <div class="col-sm-2"><?= $row['amount'] ?></div>
            <div class="col-sm-2"><?= $row['amount_package'] ?></div>
            <div class="col-sm-2"><?= round($row['amount_package'] - $row['used_sms']) ?></div><?php
            ?></div><?php
        }
        ?>
    </div>
    <?php
}

?>
