<?php
namespace app\models\billing;

use yii;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string name
 * @property string hostname
 *
 * @property string $apiUrl
 */
class Server extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const API_PORT = 8032;

    /**
     * Вернуть имена полей
     * @return array [полеВТаблице => Перевод]
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
        return Yii::$app->dbPgSlave;
    }

    public function getApiUrl()
    {
        return 'http://' . $this->hostname . ':' . self::API_PORT;
    }

}
