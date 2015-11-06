<?php

use app\classes\Html;
use kartik\grid\GridView;

if (count($result)):
    echo GridView::widget([
        'id' => 'missing_manager',
        'dataProvider' => $result,
        'columns' => [
            [
                'attribute' => 'id',
                'label' => 'ID договора',
            ],
            [
                'label' => 'Договор',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(
                        $data->contragent->name . ' / Договор № ' . $data->number . ' / ЛС № ' . $data->id,
                        ['/contract/edit', 'id' => $data->id]);
                },
            ],
        ],
        'pjax' => true,
        'toolbar'=> [],
        'bordered' => true,
        'striped' => true,
        'condensed' => true,
        'hover' => true,
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => 'Договора для которых не установлен менеджер',
        ],
    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>