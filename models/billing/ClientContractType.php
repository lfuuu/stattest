<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use Yii;

class ClientContractType extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'stat.client_contract_type';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

}