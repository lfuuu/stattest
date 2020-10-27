<?php

/** @var \app\modules\uu\models\AccountTariff $accountTariff */
$nnpPackageStat = \app\models\billing\StatsAccount::getStatsNnpPackageMinute($accountTariff->client_account_id, $accountTariff->id);

$nnpPackageStat = array_filter($nnpPackageStat, function ($row) {
    return isset($row['total_seconds']) && $row['total_seconds'] > 0;
});

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

$internetStat = $accountTariff->voip_number ? \app\models\billing\StatsAccount::getStatInternet($accountTariff->voip_number) : [];

$internetStat = array_filter($internetStat, function ($row) {
    return isset($row['bytes_consumed']) && isset($row['bytes_amount']);
});

if ($internetStat) {
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

?>
