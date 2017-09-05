<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Универсальные фильтры для грида
 *
 * @property integer $id
 * @property string $name
 * @property string $data_json доступ через data
 * @property string $model_name
 *
 * @method static FilterQuery[] findAll($condition)
 */
class FilterQuery extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    /** @var array */
    public $data;

    /**
     * @return string[]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'data' => 'Фильтры',
            'model_name' => 'Модель',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда обновил"
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false,
                'updatedAtAttribute' => 'update_time',
                'value' => new Expression("NOW() AT TIME ZONE 'utc'"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто обновил"
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'update_user_id',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'update_user_id',
                ],
                'value' => Yii::$app->user->getId(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'model_name'], 'string'],
            [['name', 'model_name', 'data'], 'required'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.filter_query';
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
     * После поднятия модели
     */
    public function afterFind()
    {
        $this->data = json_decode($this->data_json, true);
        parent::afterFind();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->data_json = json_encode($this->data);

        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
