<?php

namespace app\models;

use app\classes\model\HistoryActiveRecord;
use app\dao\ClientContactDao;
use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;

/**
 * Class ClientContact
 *
 * @property int $id
 * @property int $client_id
 * @property string $type
 * @property string $data
 * @property int $user_id
 * @property string $ts
 * @property string $comment
 * @property int $is_official
 * @property int $is_validate
 *
 * @property-read User $user
 * @property-read ClientAccount $client
 *
 * @method static ClientContact findOne($condition)
 */
class ClientContact extends HistoryActiveRecord
{
    const TYPE_PHONE = 'phone';
    const TYPE_FAX = 'fax';
    const TYPE_SMS = 'sms';

    const TYPE_EMAIL = 'email';
    const TYPE_EMAIL_INVOICE = 'email_invoice';
    const TYPE_EMAIL_RATE = 'email_rate';
    const TYPE_EMAIL_SUPPORT = 'email_support';

    // Не используется, но оставлено для совместимости, если где-то забыл выпилить обращение
    public $is_active = true;

    public static $types = [
        self::TYPE_PHONE => 'Телефон',
        self::TYPE_FAX => 'Факс',
        self::TYPE_SMS => 'СМС',

        self::TYPE_EMAIL => 'Email',
        self::TYPE_EMAIL_INVOICE => 'Email (invoice)',
        self::TYPE_EMAIL_RATE => 'Email (rate)',
        self::TYPE_EMAIL_SUPPORT => 'Email (support)',
    ];

    public static $emailTypes = [
        self::TYPE_EMAIL,
        self::TYPE_EMAIL_INVOICE,
        self::TYPE_EMAIL_RATE,
        self::TYPE_EMAIL_SUPPORT,
    ];

    public static $phoneTypes = [
        self::TYPE_PHONE,
        self::TYPE_FAX,
        self::TYPE_SMS,
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_contacts';
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
            'client_id' => 'Аккаунт',
            'type' => 'Тип',
            'data' => 'Значение',
            'user_id' => 'Кто создал',
            'ts' => 'Когда создал',
            'comment' => 'Комментарий',
            'is_official' => 'Официальный',
            'is_validate' => 'Формат проверен',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];

    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['type', 'in', 'range' => array_keys(self::$types)],
            ['is_official', 'default', 'value' => 0],
            ['is_validate', 'default', 'value' => 1],
            ['data', 'required'],
            ['data', 'trim'],
            ['comment', 'clean'],
            [
                'data',
                'email',
                'when' => function (ClientContact $model) {
                    return $model->isEmail();
                },
            ],
            [
                'data',
                'validatePhone',
                'when' => function (ClientContact $model) {
                    return $model->is_validate && $model->isPhone();
                },
            ],
            [['comment'], 'default', 'value' => ''],
            ['comment', 'string'],
            ['ts', 'default', 'value' => date(DateTimeZoneHelper::DATETIME_FORMAT)],
            ['user_id', 'default', 'value' => \Yii::$app->user->id],
            [['client_id', 'user_id', 'is_official', 'is_validate'], 'integer', 'integerOnly' => true]
        ];
    }

    public function clean()
    {
        $this->comment && $this->comment = HtmlPurifier::process($this->comment);
    }

    /**
     * @return bool
     */
    public function isEmail()
    {
        return in_array($this->type, self::$emailTypes);
    }

    /**
     * @return bool
     */
    public function isPhone()
    {
        return in_array($this->type, self::$phoneTypes);
    }

    /**
     * @param string $email
     */
    public function addEmail($email)
    {
        $this->addContact(self::TYPE_EMAIL, $email);
    }

    /**
     * @param string $phone
     */
    public function addPhone($phone)
    {
        $this->addContact(self::TYPE_PHONE, $phone);
    }

    /**
     * @param string $type
     * @param string $data
     */
    public function addContact($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_id']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * Валидировать телефон
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatePhone($attribute, $params)
    {
        if ($this->data == '.') {
            return;
        }

        list($phoneRemain, $e164Phones) = ClientContact::dao()->getE164($this->data);
        $countE164Phones = count($e164Phones);

        if ($countE164Phones == 1) {
            $e164Phone = reset($e164Phones);
            if ($this->data !== $e164Phone) {
                // Не в том формате. Автоматически привести к нужному. Нераспознанный остаток перенести в комментарий
                $this->comment .= ' ' . $phoneRemain;
                $this->data = $e164Phone;
            }

            return;
        }

        $error = 'Телефон должен быть в формате E164. Например, +799990000000.';
        if ($countE164Phones) {
            $error .= ' Возможно, надо создать несколько телефонов: ' . implode(', ', $e164Phones);
        }

        if (strpos($this->data, '@') !== false) {
            $error .= ' Возможно, это email, и надо сменить тип.';
        }

        $this->addError($attribute, $error);
    }

    /**
     * @return ClientContactDao
     */
    public static function dao()
    {
        return ClientContactDao::me();
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->client_id;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->client_id = $parentId;
    }

    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'ts':
                return (new \app\classes\DateTimeWithUserTimezone($value))->getDateTime();
            case 'user_id':
                return User::find()->where(['id' => $value])->select('name')->scalar();
            default:
                return parent::prepareHistoryValue($field, $value);
        }
    }
}
