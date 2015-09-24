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
            [['client_id', 'user_id', 'is_active', 'is_official'], 'integer', 'integerOnly' => true],
            ['comment', 'string'],
            ['ts', 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            ['type', 'in', 'range' => array_keys(self::$types)],
            ['data', 'email',
                'when' => function($model){return $model->type == self::TYPE_EMAIL;},
                'whenClient' => 'function(){return $("#contact-type").val() == "'. self::TYPE_EMAIL .'";}'
            ],
            [['comment', 'data'], 'default', 'value' => ''],
            ['is_active', 'default', 'value' => 1],
            ['is_official', 'default', 'value' => 0],
            ['ts', 'default', 'value' => date('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeValidate() {
        $this->data = trim($this->data);
        return parent::beforeValidate();
    }

}