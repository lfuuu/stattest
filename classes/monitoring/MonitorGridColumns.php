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
use app\models\Number;

abstract class MonitorGridColumns
{

    /**
     * @param array $combineChainsValue - массив указателей на свойства модели для получения clientContragent
     * @return array
     */
    public static function getStatusColumn($combineChainsValue = [])
    {
        return [
            'label' => '#',
            'format' => 'raw',
            'value' =>
                /**
                 * @param object $data - запись выборки в виде модели (Usage / ClientContract / ClientAccount)
                 * @return string
                 */
                function ($data) use ($combineChainsValue) {
                    $value = self::getCombineResult($data, $combineChainsValue);

                    return
                        Html::tag('span', '&nbsp;', [
                            'class' => 'btn btn-grid',
                            'style' => 'background: ' . ClientAccount::$statuses[$value->status]['color'],
                            'title' => ClientAccount::$statuses[$value->status]['name'],
                        ]);
                },
            'filterType' => GridView::FILTER_COLOR,
            'width' => '20px',
        ];
    }

    /**
     * @param array $combineChainsValue - массив указателей на свойства модели для получения clientContragent
     * @return array
     */
    public static function getIdColumn($combineChainsValue = [])
    {
        return [
            'attribute' => 'id',
            'label' => 'ID',
            'format' => 'raw',
            'value' =>
                /**
                 * @param object $data - запись выборки в виде модели (Usage / ClientContract / ClientAccount)
                 * @return string
                 */
                function ($data) use ($combineChainsValue) {
                    $value = self::getCombineResult($data, $combineChainsValue);
                    return
                        Html::a($value->id, ['/client/view', 'id' => $value->id]);
                },
            'width' => '80px',
        ];
    }

    /**
     * @param array $combineChainsValue - массив указателей на свойства модели для получения clientContragent
     * @param array $combineClientId - массив указателей на свойства модели для получения clientAccount
     * @return array
     */
    public static function getCompanyColumn($combineChainsValue = [], $combineClientId = [])
    {
        return [
            'label' => 'Контрагент',
            'format' => 'raw',
            'value' =>
                /**
                 * @param object $data - запись выборки в виде модели (Usage / ClientContract / ClientAccount)
                 * @return string
                 */
                function ($data) use ($combineClientId, $combineChainsValue) {
                    $client = self::getCombineResult($data, $combineClientId);
                    $value = self::getCombineResult($data, $combineChainsValue);

                    return Html::a($value->name, ['/client/view', 'id' => $client->id]);
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

    /**
     * @param array $combineChainsValue - массив указателей на свойства модели для получения clientContragent
     * @return array
     */
    public static function getManagerColumn($combineChainsValue = [])
    {
        return [
            'label' => 'Менеджер',
            'format' => 'raw',
            'value' =>
                /**
                 * @param object $data - запись выборки в виде модели (Usage / ClientContract / ClientAccount)
                 * @return string
                 */
                function ($data) use ($combineChainsValue) {
                    $value = self::getCombineResult($data, $combineChainsValue);
                    return $value->manager_name;
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
                return Html::a($data['number'], ['/usage/number/view', 'did' => $data['number']], ['target' => '_blank']);
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
                return Number::$statusList[$data['status']];
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

    /**
     * @param $source - источник данных, результат выборки или модель
     * @param array $combineChains - список свойств
     */
    private static function getCombineResult($source, $combineChains = [])
    {
        $result = $source;
        if (count($combineChains)) {
            foreach ($combineChains as $chainPart) {
                $result = $result->{$chainPart};
            }
        }
        return $result;
    }

}