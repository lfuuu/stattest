<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ClientContractDao;

class ClientContract extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contracts';
    }

    public static function dao()
    {
        return ClientContractDao::me();
    }
}
