<?php
namespace app\models;

use yii\db\ActiveRecord;
class ClientGridBussinesProcess extends ActiveRecord
{
    public static function tableName()
    {
        return 'grid_business_process';
    }
}
