<?php
namespace app\models;

use app\dao\TroubleDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class Trouble extends ActiveRecord
{
    public static function tableName()
    {
        return 'tt_troubles';
    }

    public static function dao()
    {
        return TroubleDao::me();
    }
}