<?php

namespace app\classes\uu\model;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Бухгалтерская проводка
 * Объединяет предварительное списание (транзакции) для одной услуги и типу (подключение, абонентка, каждый ресурс) по календарным месяцам
 *
 * @property int $id
 * @property string $date важен только месяц. День всегда 1.
 * @property int $account_tariff_id
 * @property int $type_id Если положительное, то TariffResource, иначе подключение или абонентка. Поэтому нет FK
 * @property float $price
 * @property float $price_without_vat
 * @property int $vat_rate
 * @property float $vat
 * @property float $price_with_vat
 * @property string $update_time
 * @property int $is_updated
 *
 * @property AccountTariff $accountTariff
 * @property \app\classes\uu\model\TariffResource $tariffResource
 * @property AccountLogSetup[] $accountLogSetups
 * @property AccountLogPeriod[] $accountLogPeriods
 * @property AccountLogResource[] $accountLogResources
 * @property string typeName
 * @property string code
 */
class AccountEntry extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const TYPE_ID_SETUP = -1;
    const TYPE_ID_PERIOD = -2;
    const TYPE_ID_MIN = -3;

    const CODE_TYPE_ID_SETUP = 'setup';
    const CODE_TYPE_ID_PERIOD = 'period';
    const CODE_TYPE_ID_MINIMUM = 'minimum';
    const CODE_TYPE_ID_RESOURCES = 'resource';

    private $codeNames = [
        self::TYPE_ID_SETUP => self::CODE_TYPE_ID_SETUP,
        self::TYPE_ID_PERIOD => self::CODE_TYPE_ID_PERIOD,
        self::TYPE_ID_MIN => self::CODE_TYPE_ID_MINIMUM,
    ];

    public static function tableName()
    {
        return 'uu_account_entry';
    }

    public function rules()
    {
        return [
            [['account_tariff_id', 'type_id'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::className(), ['account_entry_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        if (!isset($this->codeNames[$this->type_id])) {
            return self::CODE_TYPE_ID_RESOURCES;
        }
        return $this->codeNames[$this->type_id];
    }

    /**
     * Вернуть тип текстом
     * @return string
     */
    public function getTypeName()
    {
        $tableName = self::tableName();
        switch ($this->type_id) {
            case self::TYPE_ID_SETUP:
            case self::TYPE_ID_PERIOD:
            case self::TYPE_ID_MIN:

            $serviceTypeName = '';
            if (
                ($accountTariff = $this->accountTariff)
                && ($serviceType = $accountTariff->serviceType)
            ) {
                $serviceTypeName = $serviceType->name . '. ';
            }

            $name = Yii::t(
                'models/' . $tableName,
                $this->code
            );

            return $serviceTypeName . $name;

            default: //resources
                if (
                    ($tariffResource = $this->tariffResource) &&
                    ($resource = $tariffResource->resource)
                ) {
                    return $resource->getFullName();
                } else {
                    Yii::error('Wrong AccountEntry.Type ' . $this->type_id . ' for ID ' . $this->id);
                    return '';
                }
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/account-entry', 'AccountEntryFilter[id]' => $this->id]);
    }
}
