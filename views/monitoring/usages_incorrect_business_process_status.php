<?php

use app\classes\Html;
use kartik\grid\GridView;

if (count($result)):
    echo GridView::widget([
        'id' => 'usages_incorrect_business_process_status',
        'dataProvider' => $result,
        'columns' => [
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(
                        $data->clientAccount->contract->contragent->name . ' / Договор № ' . $data->clientAccount->contract->number . ' / ЛС № ' . $data->clientAccount->id,
                        ['/client/view', 'id' => $data->clientAccount->id],
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
            'heading' =>
                'Лицевые счета с активными услугами и бизнес-процесс статусом не ' .
                '"Включенные", "Подключаемые", "Заказ услуг"',
        ],
    ]);
else: ?>
    <div class="well" style="text-align: center;">
        <div class="alert alert-info">
            Подозрительного не найдено
        </div>
    </div>
<?php endif; ?>