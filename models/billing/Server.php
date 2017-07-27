<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use yii;

/**
 * @property int $id
 * @property string $name
 * @property string $hostname
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
     *
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

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public.server';
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

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return 'http://' . $this->hostname . ':' . self::API_PORT;
    }

}
