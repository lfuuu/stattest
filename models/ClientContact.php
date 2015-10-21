<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientContact extends ActiveRecord
{
    const TYPE_PHONE = 'phone';
    const TYPE_FAX = 'fax';
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';

    public static $types =[
        self::TYPE_PHONE => 'Телефон',
        self::TYPE_FAX => 'Факс',
        self::TYPE_EMAIL => 'Email',
        self::TYPE_SMS => 'СМС',
    ];

    public static function tableName()
    {
        return 'client_contacts';
    }

    public function rules()
    {
        return [
            ['type', 'in', 'range' => array_keys(self::$types)],
            ['data', 'required'],
            ['data', 'trim'],
            ['data', 'email',
                'when' => function($model){return $model->type == self::TYPE_EMAIL;},
                'whenClient' => 'function(){return $("#contact-type").val() == "'. self::TYPE_EMAIL .'";}'
            ],
            [['comment'], 'default', 'value' => ''],
            ['comment', 'string'],
            ['is_active', 'default', 'value' => 1],
            ['is_official', 'default', 'value' => 0],
            ['ts', 'default', 'value' => date('Y-m-d H:i:s')],
            ['user_id', 'default', 'value' => \Yii::$app->user->id],
            [['client_id', 'user_id', 'is_active', 'is_official'], 'integer', 'integerOnly' => true]
        ];
    }

    public function addEmail($email)
    {
        $this->addContact(self::TYPE_EMAIL, $email);
    }

    public function addContact($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function setActiveAndOfficial()
    {
        $this->is_active = 1;
        $this->is_official = 1;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
