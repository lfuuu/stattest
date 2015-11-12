<?php

namespace app\classes\grid\column\notifications;

use kartik\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\DataColumn;
use app\classes\notifications\NotificationFactory;
use app\classes\notifications\AddPaymentNotification;
use app\classes\notifications\PrebilPrepayersNotification;

class NotificationNameColumn extends DataColumn
{

    public $label = 'Событие';
    public $attribute = 'event';
    public $value = 'event';
    public $filterType = GridView::FILTER_SELECT2;

    private $values = [
        '' => '-- Выберите событие --',
        'min_balance' => 'Критический остаток',
        'zero_balance' => 'Финансовая блокировка',
        'day_limit' => 'Суточный лимит',
        'add_pay_notif' => 'Зачисление средств',
        'prebil_prepayers_notif' => 'Списание абонентской платы авансовым клиентам',
    ];

    public function __construct($config = [])
    {
        $this->filter = $this->values;
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $eventKey = parent::getDataCellValue($model, $key, $index);
        $value = $this->values[$eventKey] ?: $eventKey;

        $info = [
            'style' => 'background: url("/images/icons/action_delete.gif") 100% 0 no-repeat;',
            'title' => 'Установлено',
        ];
        if (!$model['is_set'] && $eventKey != 'add_pay_notif' && $eventKey != 'prebil_prepayers_notif') {
            $info = [
                'style' => 'background: url("/images/icons/action_check.gif") 100% 0 no-repeat;',
                'title' => 'Снято',
            ];
        }

        return
            Html::beginTag('div', $info) .
                $value .
            Html::endTag('div');
    }
}