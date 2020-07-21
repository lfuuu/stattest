<?php

use app\modules\nnp2\models\RangeShort;

/** @var RangeShort $filterModel */
?>
<div class="alert alert-info">
    Время последнего обновления: <strong><?=$filterModel->getLastLogDate();?></strong>
</div>
