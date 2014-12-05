<?php
namespace app\forms\support;

use app\classes\enum\ServiceTypeEnum;
use app\classes\enum\TicketStatusEnum;
use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\CoreUserIdValidator;
use app\classes\validators\EnumValidator;
use app\classes\validators\TicketIdValidator;

class TicketForm extends Form
{
    public $id;
    public $client_account_id;
    public $user_id;
    public $service_type;
    public $subject;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['id'], TicketIdValidator::className()],
            [['client_account_id'], AccountIdValidator::className()],
            [['user_id'], CoreUserIdValidator::className()],
            [['service_type'], EnumValidator::className(), 'enum' => ServiceTypeEnum::className()],
            [['subject'], 'string', 'max' => 1000],
            [['description'], 'string'],
            [['status'], EnumValidator::className(), 'enum' => TicketStatusEnum::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'service_type' => 'Тип услуги',
            'subject' => 'Название',
            'description' => 'Описание',
            'status' => 'Статус',
        ];
    }
}