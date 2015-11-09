<?php

namespace app\classes\monitoring;

use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageExtra;
use app\models\UsageWelltime;

class UsagesActiveConnecting extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'usages_active_connecting';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Активные услуги с некорректным статусом';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Список активных услуг со статусом "подключаемые"';
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'attribute' => 'id',
                'label' => 'ID услуги',
            ],
            MonitorGridColumns::getClient(),
            MonitorGridColumns::getUsageTitle(),
            MonitorGridColumns::getUsageRelevance(),
            MonitorGridColumns::getUsageDescription(),
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $usages = [
            UsageVoip::className(),
            UsageVirtpbx::className(),
            UsageIpPorts::className(),
            UsageSms::className(),
            UsageExtra::className(),
            UsageWelltime::className(),
        ];

        $result = [];
        foreach ($usages as $usage) {
            $result = array_merge(
                $result,
                (array) $usage::find()
                    ->where(new Expression('actual_from <= CAST(NOW() AS DATE)'))
                    ->andWhere(new Expression('actual_to > CAST(NOW() AS DATE)'))
                    ->andWhere(['status' => 'connecting'])
                    ->all()
            );
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}