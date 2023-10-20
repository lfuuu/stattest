<?php

$extLog = $imsi->externalStatusLog;
if (!$extLog) {
    return '';
}
?>
<div class="well" style="padding-bottom: 40px;">
    <h1>Внешний лог состояния IMSI <?=$imsi->imsi?></h1>
    <div class="col-md-<?= round(12 / $count) ?>">
        <?php
        foreach ($imsi->externalStatusLog as $log) {
            ?>
            <?= $log->statusString ?>
            <?php
        }
        ?>
    </div>
</div>

