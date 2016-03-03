<?php
namespace app\models\billing;

use yii;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string name
 * @property string hostname
 */
class Server extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'hostname' => 'Хост',
        ];
    }

    public static function tableName()
    {
        return 'public.server';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }
}
