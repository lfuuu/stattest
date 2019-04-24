<?php

namespace app\modules\uu\models\billing_uu;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Прайслисты API
 *
 * @property int $id
 * @property string $name
 */
class PricelistApi extends ActiveRecord
{
    use \app\classes\traits\GetListTrait;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'billing_api.api_pricelist';
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
    public function __toString()
    {
        return $this->name;
    }
}