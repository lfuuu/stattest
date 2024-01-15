<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use yii;

/**
 * @property int $id
 * @property string $name
 * @property string $hostname
 * @property int $hub_id
 *
 * @property-read Hub $hub
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
            'hub_id' => 'Хаб',
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
     * @return \yii\db\ActiveQuery
     */
    public function getHub()
    {
        return $this->hasOne(Hub::class, ['id' => 'hub_id']);
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return 'http://' . $this->hostname . ':' . self::API_PORT;
    }

    public static function getMgmnServerId($serverId)
    {
        return self::find()
            ->select('id')
            ->where([
                'hub_id' => self::find()
                    ->select('hub_id')
                    ->where(['id' => $serverId])
                    ->cache(1000)
                    ->scalar()
            ])
            ->andWhere(new yii\db\Expression("coalesce(mg_spc::varchar, '') != ''"))
            ->cache(1000)
            ->scalar();
    }

}
