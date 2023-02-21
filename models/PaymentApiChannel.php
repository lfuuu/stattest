<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetListTrait;
use app\classes\Utils;
use app\classes\validators\FormFieldValidator;
use yii\helpers\Url;

/**
 * @property int $id             идентификатор платежа
 * @property int $code
 * @property int $access_token
 * @property int $name
 * @property int $is_active
 * @property int $check_organization_id
 *
 * @method static PaymentApiChannel findOne($condition)
 * @method static PaymentApiChannel[] findAll($condition)
 */
class PaymentApiChannel extends ActiveRecord
{
    const NAVIGATION = 'payment-api-channel';
    const TITLE = 'API Каналы платежей';

    use GetListTrait {
        getList as getListTrait;
    }

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'newpayment_api_channel';
    }


    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'check_organization_id', 'is_active'], 'integer'],
            [['code', 'access_token', 'name'], 'string'],
            [['code', 'name', 'is_active'], 'required'],
            [['code', 'access_token', 'name'], FormFieldValidator::class],
            ['code', 'codeValidator'],
        ];
    }

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
            'code' => 'Код канала',
            'access_token' => 'Токен',
            'is_active' => 'Активен',
            'check_organization_id' => 'Чек от орагнизации',
        ];
    }

    public function codeValidator($attr)
    {
        if (
        self::find()
            ->where(['code' => $this->code])
            ->andWhere(['NOT', ['id' => $this->id]])
            ->exists()
        ) {
            $this->addError($attr, 'такой Code уже существует');
        }
    }

    public function beforeSave($insert)
    {
        if (!$this->access_token) {
            $this->access_token = Utils::gen_password(32);
        }
        return parent::beforeSave($insert);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    )
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'code',
            $select = 'name',
            $orderBy = ['id' => SORT_ASC],
            $where = []
        );
    }

    public function isUsedCode()
    {
        return Payment::find()->where([
            'type' => Payment::TYPE_API,
            'ecash_operator' => $this->code
        ])->exists();
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
        return Url::to(['/dictionary/' . self::NAVIGATION . '/edit', 'id' => $id]);
    }

}
