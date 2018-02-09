<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\helpers\Url;

/**
 * Типы VM
 *
 * @link http://datacenter.mcn.ru/vps-hosting/
 *
 * @property int $id
 * @property string $name
 *
 * @method static TariffVm findOne($condition)
 * @method static TariffVm[] findAll($condition)
 */
class TariffVm extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_vm';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['name'], 'required'],
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/uu/tariff-vm/edit', 'id' => $id]);
    }
}