<?php

use kartik\grid\GridView;
use app\classes\Html;

if (count($result)):
    echo GridView::widget([
        'id' => 'usage_lost_tariffs',
        'dataProvider' => $result,
        'columns' => [
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(
                        $data->contract->contragent->name . ' / Договор № ' . $data->contract->number . ' / ЛС № ' . $data->id,
                        ['/client/view', 'id' => $data->id],
                        [
                            'target' => '_blank'
                        ]
                    );
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
            'heading' => 'Лицевые счета с отключенным кредитом',
        ],
    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>