<?php

namespace app\classes\monitoring;

use Yii;
use DateTime;
use kartik\grid\GridView;
use app\classes\Html;
use kartik\daterange\DateRangePicker;
use app\helpers\DateTimeZoneHelper;
use app\models\User;
use app\models\ClientAccount;
use app\models\Region;

abstract class MonitorGridColumns
{

    public static function getStatusColumn()
    {
        return [
            'attribute' => 'status',
            'label' => '#',
            'format' => 'raw',
            'value' => function ($data) {
                return
                    Html::tag('span', '&nbsp;', [
                        'class' => 'btn btn-grid',
                        'style' => 'background: ' . ClientAccount::$statuses[$data->status]['color'],
                        'title' => ClientAccount::$statuses[$data->status]['name'],
                    ]);
            },
            'filterType' => GridView::FILTER_COLOR
        ];
    }

    public static function getIdColumn()
    {
        return [
            'attribute' => 'id',
            'label' => 'ID',
            'format' => 'raw',
            'value' => function ($data) {
                return
                    Html::a($data->id, ['/client/view', 'id' => $data->id]);
            },
            'width' => '120px',
        ];
    }

    public static function getCompanyColumn()
    {
        return [
            'attribute' => 'company',
            'label' => 'Контрагент',
            'format' => 'raw',
            'value' => function ($data) {
                return
                    Html::a($data->company, ['/client/view', 'id' => $data->id]);
            },
            'width' => '500px',
        ];
    }

    public static function getCreatedColumn()
    {
        return [
            'attribute' => 'created',
            'label' => 'Заведен',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->created ? DateTimeZoneHelper::getDateTime($data->created) : null;
            },
            'filter' =>
                DateRangePicker::widget([
                    'name' => 'created',
                    'presetDropdown' => true,
                    'hideInput' => true,
                    'value' => Yii::$app->request->get('created'),
                    'pluginOptions' => [
                        'format' => 'YYYY-MM-DD',
                    ],
                    'containerOptions' => [
                        'style' => 'width: 265px; overflow: hidden;',
                        'class' => 'drp-container input-group',
                    ]
                ])
        ];
    }

    public static function getManagerColumn()
    {
        return [
            'label' => 'Менеджер',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->manager_name;
            },
            'filter' =>
                Html::dropDownList(
                    'manager',
                    Yii::$app->request->get('manager'),
                    array_merge(['' => '-- Менеджер --'], User::getManagerList()),
                    ['class' => 'form-control select2']
                )
        ];
    }

    public static function getRegionColumn()
    {
        return [
            'label' => 'Регион',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->regionName;
            },
            'filter' =>
                Html::dropDownList(
                    'region',
                    Yii::$app->request->get('region'),
                    Region::dao()->getList(true),
                    ['class' => 'form-control select2']
                ),
        ];
    }

    public static function getVoipNumber()
    {
        return [
            'label' => 'Номер',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->number, ['/usage/number/view', 'did' => $data->number], ['target' => '_blank']);
            },
        ];
    }

    public static function getVoipNumberStatus()
    {
        return [
            'attribute' => 'status',
            'label' => 'Статус',
            'format' => 'raw',
            'value' => function($data) {
                if ($data->status == 'instock') {
                    return 'В продаже';
                }
                if ($data->status == 'active') {
                    return 'Используется';
                }
            },
        ];
    }

    public static function getClient()
    {
        return [
            'label' => 'Клиент',
            'format' => 'raw',
            'value' => function($data) {
                return Html::a(
                    $data->clientAccount->contract->contragent->name .
                    ' / Договор № ' . $data->clientAccount->contract->number .
                    ' / ЛС № ' . $data->clientAccount->id,
                    ['/client/view', 'id' => $data->clientAccount->id],
                    [
                        'target' => '_blank',
                    ]
                );
            },
        ];
    }

    public static function getUsageRelevance()
    {
        return [
            'label' => 'Актуальность',
            'format' => 'raw',
            'value' => function($data) {
                return
                    DateTimeZoneHelper::getDateTime($data->actual_from)
                    . ' -> ' .
                    (
                        round(
                            (
                            (new DateTime($data->actual_to))->getTimestamp() - (new DateTime('now'))->getTimestamp()
                            ) / 365 / 24 / pow(60, 2)
                        ) > 20
                            ? '&#8734' :
                            DateTimeZoneHelper::getDateTime($data->actual_to)
                    );
            },
        ];
    }

    public static function getUsageTitle()
    {
        return [
            'label' => 'Тип услуги',
            'format' => 'raw',
            'value' => function($data) {
                return $data->getTransferHelper($data)->getTypeTitle();
            }
        ];
    }

    public static function getUsageDescription()
    {
        return [
            'label' => 'Описание услуги',
            'format' => 'raw',
            'value' => function($data) {
                list ($title, $description, $other) = (array) $data->getTransferHelper($data)->getTypeDescription();
                return $title . ' ' . $description;
            }
        ];
    }

}