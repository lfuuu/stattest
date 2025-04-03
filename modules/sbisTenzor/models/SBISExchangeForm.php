<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use Yii;

/**
 * Форма первичного документа (файла) в системе документооборота
 *
 * @property integer $id
 * @property string $type
 * @property string $name
 * @property string $full_name
 * @property string $version
 * @property integer $knd_code
 * @property string $file_pattern
 * @property string $starts_at
 * @property string $expires_at
 */
class SBISExchangeForm extends ActiveRecord
{
    // акты
    const ACT_2016_5_02 = 1;
    // счета-фактуры
    const INVOICE_2016_5_02 = 2;
    const INVOICE_2019_5_01 = 3;
    const INVOICE_2025_5_03 = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_exchange_form';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'full_name', 'version', 'knd_code', 'file_pattern'], 'required'],
            [['knd_code'], 'integer'],
            [['starts_at', 'expires_at'], 'safe'],
            [['type'], 'string', 'max' => 15],
            [['name', 'full_name', 'file_pattern'], 'string', 'max' => 255],
            [['version'], 'string', 'max' => 5],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Тип формы',
            'name' => 'Название',
            'full_name' => 'Название (полное)',
            'version' => 'Версия',
            'knd_code' => 'Код КНД',
            'file_pattern' => 'Шаблон идентификатора файла',
            'starts_at' => 'Действует с',
            'expires_at' => 'Действует до',
        ];
    }
}