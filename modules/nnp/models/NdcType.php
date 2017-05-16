<?php

namespace app\modules\nnp\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 */
class NdcType extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const ID_GEOGRAPHIC = 1;
    const ID_MOBILE = 2;
    const ID_NOMADIC = 3;
    const ID_FREEPHONE = 4;
    const ID_PREMIUM = 5;
    const ID_SHORT_CODE = 6;
    const ID_REST = 7;

    const DEFAULT_HOLD = '6 month';

    private static $_holdList = [
        self::ID_FREEPHONE => '1 day',
    ];

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
        return 'nnp.ndc_type';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/ndc-type/edit', 'id' => $id]);
    }

    /**
     * Возвращает время "отстоя" номера
     *
     * @return \DateInterval
     */
    public function getHold()
    {
        if (isset(self::$_holdList[$this->id])) {
            $interval = self::$_holdList[$this->id];
        } else {
            $interval = self::DEFAULT_HOLD;
        }

        return \DateInterval::createFromDateString($interval);
    }
}