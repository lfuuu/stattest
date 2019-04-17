<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Прайслисты v.2
 *
 * @property int $id
 * @property string $name
 * @property string $currency_id
 * @property bool $orig
 * @property string $date_created
 * @property string $date_start
 * @property string $date_end
 * @property int $pricelist_version
 * @property bool $is_global
 * @property bool $is_active
 * @property int $pricelist_group_id
 * @property int $type_id
 */
class Pricelist extends ActiveRecord
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
            'currency_id' => 'Валюта',
            'orig' => 'Оргигинация?',
            'date_created' => 'Дата создания',
            'date_start' => 'Дата начала',
            'date_end' => 'Дата окончания',
            'pricelist_version' => 'Версия прайслиста',
            'is_global' => 'Глобальный?',
            'is_active' => 'Активный?',
            'pricelist_group_id' => 'Группа прайслиста',
            'type_id' => 'Тип',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.pricelist';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id'], 'required'],
            [['is_termination', 'is_include_vat'], 'boolean'],
            [
                [
                    'tariff_id',
                    'tarification_free_seconds',
                    'tarification_interval_seconds',
                    'tarification_type',
                    'tarification_min_paid_seconds',
                    'location_id',
                ],
                'integer'
            ],
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
    public function __toString()
    {
        return $this->name;
    }
}