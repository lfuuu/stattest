<?php

namespace app\classes\monitoring;

use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\classes\Html;
use app\models\UsageVoip;
use app\models\LogTarif;

class UsageVoipNotFilledTariffs extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_not_filled_tariffs';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Телефония с неполным набором тарифов';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Телефония с неполным набором тарифов';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'ID услуги',
                'format' => 'raw',
                'value' => function($data) {
                    $usage = UsageVoip::findOne($data['id']);

                    return
                        Html::a(
                            $usage->id,
                            $usage->helper->editLink,
                            ['target' => '_blank']
                        );
                },

            ],
            [
                'label' => 'Клиент',
                'format' => 'raw',
                'value' => function($data) {
                    $usage = UsageVoip::findOne($data['id']);

                    return
                        Html::a(
                            $usage->clientAccount->contract->contragent->name .
                            ' / Договор № ' . $usage->clientAccount->contract->number .
                            ' / ЛС № ' . $usage->clientAccount->id,
                            ['/client/view', 'id' => $usage->clientAccount->id],
                            ['target' => '_blank']
                        );
                }
            ],
            [
                'label' => 'Отсутствует информация',
                'format' => 'raw',
                'value' => function($data) {
                    $result = [];

                    if (!$data['id_tarif']) {
                        $result[] = 'Тариф Основной';
                    }
                    if (!$data['id_tarif_local_mob']) {
                        $result[] = 'Тариф Местные мобильные';
                    }
                    if (!$data['id_tarif_russia']) {
                        $result[] = 'Тариф Россия стационарные';
                    }
                    if (!$data['id_tarif_russia_mob']) {
                        $result[] = 'Тариф Россия мобильные';
                    }
                    if (!$data['id_tarif_intern']) {
                        $result[] = 'Тариф Международка';
                    }

                    return implode('<br />', $result);
                }
            ],
            [
                'label' => 'Активация тарифа',
                'format' => 'raw',
                'value' => function($data) {
                    return $data['date_activation'];
                }
            ],
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $result =
            UsageVoip::find()
                ->from([UsageVoip::tableName() . ' uv'])
                ->select([
                    'uv.id',
                    't.id_tarif',
                    't.id_tarif_local_mob',
                    't.id_tarif_russia',
                    't.id_tarif_russia_mob',
                    't.id_tarif_intern',
                    't.date_activation',
                ])
                ->leftJoin(LogTarif::tableName() . ' t', 't.id_service = uv.id AND t.service="usage_voip"')
                ->where(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->orWhere(new Expression('uv.actual_to > DATE_ADD(NOW(), INTERVAL -1 MONTH)'))
                ->andWhere(new Expression('!t.id_tarif OR !t.id_tarif_local_mob OR !t.id_tarif_russia OR !t.id_tarif_russia_mob OR !t.id_tarif_intern'))
                ->asArray()
                ->all();

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}