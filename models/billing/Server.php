<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 1/11/16
 * Time: 5:02 PM
 */

namespace app\models\billing;

use yii;
use yii\db\ActiveRecord;

class Server extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.server';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return OperatorDao::me();
    }
}