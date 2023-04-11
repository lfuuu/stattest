<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Данные по контрагенту в системе СБИС
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $accounts
 * @property string $tin     Идентификационный номер налогоплательщика (ИНН) (для ЮЛ)
 * @property string $itn     Идентификационный номер налогоплательщика (ИНН) (для ФЛ и ИП)
 * @property string $iec     Код причины постановки (КПП)
 * @property string $full_name
 * @property string $branch_code     Код филиала
 * @property bool $is_roaming
 * @property string $email
 * @property string $phone
 * @property string $exchange_id
 * @property string $fixed_exchange_id
 * @property string $exchange_id_is
 * @property string $exchange_id_spp
 * @property integer $country_code
 * @property integer $is_private
 * @property string $inila   Страховой Номер Индивидуального Лицевого Счёта (СНИЛС)
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 *
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read ClientAccount $clientAccount
 */
class SBISContractor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_contractor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['full_name', 'is_roaming', 'is_private'], 'required'],
            [['account_id', 'country_code'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['tin'], 'string', 'max' => 10],
            [['itn'], 'string', 'max' => 12],
            [['iec'], 'string', 'max' => 36],
            [['full_name', 'email', 'phone', 'exchange_id_is', 'exchange_id_spp'], 'string', 'max' => 255],
            [['exchange_id', 'fixed_exchange_id'], 'string', 'max' => 46],
            [['branch_code'], 'string', 'max' => 8],
            [['inila'], 'string', 'max' => 15],
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 60],
            [['accounts'], 'string'],
            [
                ['tin', 'itn', 'iec', 'inila', 'branch_code'], 'unique', 'targetAttribute' => ['tin', 'itn', 'iec', 'inila', 'branch_code'],
                'message' => 'Данная комбинация реквизитов из {attributes} (поля необязательные) {values} уже закреплена за другим контрагентом!',
                'when' => function ($model, $attribute) {
                    // убираем дублирующиеся ошибки для каждого из полей
                    foreach (['tin', 'itn', 'iec', 'inila'] as $property) {
                        if (!empty($model->$property)) {
                            return $property === $attribute;
                        }
                    }
                    return true;
                }
            ],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Клиент',
            'accounts' => 'Клиенты',
            'tin' => 'ИНН для ЮЛ',
            'itn' => 'ИНН для ФЛ и ИП',
            'iec' => 'КПП',
            'full_name' => 'Название',
            'branch_code' => 'Код филиала',
            'is_roaming' => 'Включён роуминг',
            'email' => 'Email',
            'phone' => 'Телефон',
            'exchange_id' => 'Идентификатор',
            'fixed_exchange_id' => 'Идентификатор (фиксированный)',
            'exchange_id_is' => 'ИдентификаторИС',
            'exchange_id_spp' => 'ИдентификаторСПП',
            'country_code' => 'Код страны',
            'is_private' => 'Частное лицо',
            'inila' => 'СНИЛС',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'created_at' => 'Добавлен',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'account_id']);
    }

    /**
     * Получить идентификатор ЭДО
     *
     * @return string
     */
    public function getEdfId()
    {
        return $this->fixed_exchange_id ? : $this->exchange_id;
    }

    /**
     * Добавить Id клиента
     *
     * @param $accountId
     */
    public function addAccountId($accountId)
    {
        $accounts = json_decode($this->accounts ? : '');

        $accounts = array_combine($accounts, $accounts);
        $accounts[$accountId] = $accountId;

        $this->accounts = json_encode(array_keys($accounts));
    }

}
