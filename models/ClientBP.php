<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientBP extends ActiveRecord
{
    const TELEKOM__SALE = 2; //Продажи
    const TELEKOM__SUPPORT = 1; //Сопровождение

    public static function tableName()
    {
        return 'grid_business_process';
    }
}
