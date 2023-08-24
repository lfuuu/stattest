<?php

namespace app\modules\sorm\filters;

use app\classes\model\ActiveRecord;

class SormClientsFilter extends ActiveRecord
{
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
            'allModels' => \Yii::$app->dbPg->createCommand('select * from sorm_itgrad.subscribers_v1 where not is_active and legal_type_id = 0')->queryAll()
        ]);

        return $dataProvider;
    }

}
