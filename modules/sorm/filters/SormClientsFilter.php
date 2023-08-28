<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;
use app\models\ClientContragent;

class SormClientsFilter extends ActiveRecord
{
    public $type = ClientContragent::PERSON_TYPE;

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
            'allModels' => \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.subscribers_v1 where not is_active and legal_type_id = :legal_type_id', [':legal_type_id' => $this->getLegalTypeId()])->queryAll()
        ]);

        return $dataProvider;
    }

    private function getLegalTypeId()
    {
        return $this->legalMap[$this->type] ?? 1;
    }
}
