<?php
namespace app\models;

use app\classes\model\HistoryActiveRecord;

class ClientContragentPerson extends HistoryActiveRecord
{

    public static function tableName()
    {
        return 'client_contragent_person';
    }

    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];
    }
}
