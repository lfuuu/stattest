<?php

namespace app\classes\monitoring;

use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use app\models\VoipNumber;
use app\models\UsageVoip;

class VoipNumbersIntegrity extends Component implements MonitoringInterface
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'voip_numbers_integrity';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Расхождение между номерами и услугами';
    }

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $result = [];

        $result = array_merge(
            $result,
            VoipNumber::find()
                ->leftJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = number')
                ->where([VoipNumber::tableName() . '.status' => 'active'])
                ->andWhere('uv.id IS NULL')
                ->all()
        );

        $result = array_merge(
            $result,
            VoipNumber::find()
                ->rightJoin(UsageVoip::tableName() . ' uv', 'uv.E164 = number')
                ->where([VoipNumber::tableName() . '.status' => 'instock'])
                ->andWhere(new Expression('uv.actual_from <= CAST(NOW() AS DATE)'))
                ->andWhere(new Expression('uv.actual_to > CAST(NOW() AS DATE)'))
                ->all()
        );

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}