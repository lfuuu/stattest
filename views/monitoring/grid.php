<?php

use kartik\grid\GridView;

$result = $monitor->result;

if (count($result)):
    echo GridView::widget([
        'id' => $monitor->key,
        'dataProvider' => $result,
        'filterModel' => $monitor,
        'columns' => $monitor->columns,
        'toolbar'=> [],
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => $monitor->description,
        ],
        'pjax' => false,
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'hover' => true,

    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>

<style type="text/css">
.panel-heading .panel-title {
    font-size: 12px;
    margin-top: 4px;
}
</style>