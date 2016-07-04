<?php

namespace app\classes\uu\model;

use app\classes\Html;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Лог тарифов универсальной услуги
 *
 * @property int $id
 * @property int $account_tariff_id
 * @property int $tariff_period_id если null, то закрыто
 * @property string $actual_from !
 *
 * @property TariffPeriod $tariffPeriod
 */
class AccountTariffLog extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits {
        attributeLabels as attributeLabelsFromTrait;
    }

    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    public $tariffPeriodFieldName = '';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id', 'tariff_period_id'], 'integer'],
            [['account_tariff_id', 'actual_from'], 'required'],
            ['actual_from', 'date', 'format' => 'php:Y-m-d'],
            ['actual_from', 'validatorFuture'],
            ['actual_from', 'validatorOneInFuture'],
        ];
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        $attributeLabels = $this->attributeLabelsFromTrait();
        $this->tariffPeriodFieldName && $attributeLabels['tariff_period_id'] = $this->tariffPeriodFieldName;
        return $attributeLabels;
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::className(), ['id' => 'tariff_period_id']);
    }

    /**
     * Вернуть сгенерированное имя
     * @return string
     */
    public function getName()
    {
        return $this->tariffPeriod ?
            $this->tariffPeriod->getName() :
            Yii::t('common', 'Switched off');
    }

    /**
     * Вернуть html: имя + ссылка на тариф
     * @return string
     */
    public function getTariffPeriodLink()
    {
        return $this->tariff_period_id ?
            Html::a(
                Html::encode($this->getName()),
                $this->tariffPeriod->getUrl()
            ) :
            Yii::t('common', 'Switched off');
    }

    /**
     * Валидировать, что дата смены тарифа в будущем
     * @param string $attribute
     * @param [] $params
     */
    public function validatorFuture($attribute, $params)
    {
        if (!$this->isNewRecord) {
            return;
        }

        $currentDate = date('Y-m-d');
        if ($this->actual_from > $currentDate) {
            return;
        }
        if ($this->actual_from < $currentDate) {
            $this->addError($attribute, 'Нельзя менять тариф задним числом.');
        }

        // С сегодня. При подключени нового это можно, но при смене существующего нельзя
        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->count()
        ) {
            $this->addError($attribute, 'Сменить тариф можно только с завтра или позже.');
        }
    }

    /**
     * Валидировать, что в будущем не более одной смены тарифа
     * @param string $attribute
     * @param [] $params
     */
    public function validatorOneInFuture($attribute, $params)
    {
        if (!$this->isNewRecord) {
            return;
        }
        if (self::find()
            ->where(['account_tariff_id' => $this->account_tariff_id])
            ->andWhere(['>=', 'actual_from', new Expression('now()')])
            ->count()
        ) {
            $this->addError($attribute, 'Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените предыдущую смену.');
        }
    }
}