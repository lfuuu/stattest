<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\ClientContragent;

class SormClientsFilter extends ActiveRecord
{
    public $type = ClientContragent::PERSON_TYPE;
    public $isB2c = true;

    private $legalMap = [
        ClientContragent::PERSON_TYPE => 0,
        ClientContragent::LEGAL_TYPE => 1,
    ];

    public function attributes()
    {
        return [
            'id' => 'Ид',
            'name_jur' => 'Название / ФИО',
        ];
    }

    public function search()
    {
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $this->getSubscribers(),
        ]);

        return $dataProvider;
    }

    private function getSubscribers()
    {
        $subscribers = \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.subscribers_v1 where not is_active and legal_type_id = :legal_type_id', [':legal_type_id' => $this->getLegalTypeId()])->queryAll();

        if (!$subscribers) {
            return [];
        }

        $accountIds = array_map(fn($sub) => $sub['id'], $subscribers);
        $accountIdsB2c = ClientAccount::find()->where(['id' => $accountIds, 'price_level' => ClientAccount::PRICE_LEVEL_B2C])->select('id')->column();

        if (!$accountIdsB2c) {
            return [];
        }

        $accountIdsB2c = array_fill_keys($accountIdsB2c, 1); // array values to keys
        return array_filter($subscribers, function ($sub) use ($accountIdsB2c) {
            $isSet = isset($accountIdsB2c[$sub['id']]);
            return $this->isB2c ? $isSet : !$isSet;
        });

    }

    private function getLegalTypeId()
    {
        return $this->legalMap[$this->type] ?? 1;
    }
}
