<?php

$extLog = $imsi->externalStatusLog;
if (!$extLog) {
    return '';
}
?>
<div class="well">
    <h1>Внешний лог состояния IMSI <?= $imsi->imsi ?></h1>
    <div class="row" style="border: 1px solid #ddd; border-radius: 5px; min-height: 100px; height: 100px; overflow-y: scroll;">
        <div class="col-md-12">
            <?php
            /** @var \app\modules\sim\models\ImsiExternalStatusLog $log */
            foreach ($imsi->getExternalStatusLog()->orderBy(['id' => SORT_DESC])->each() as $log) {
                echo $log->statusStringHtml;
            }
            ?>
        </div>
    </div>
</div>

