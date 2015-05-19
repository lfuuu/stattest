<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ClientContactDao;

class ClientContact extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contacts';
    }

    public static function dao()
    {
        return ClientContactDao::me();
    }
}
