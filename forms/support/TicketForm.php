<?php
namespace app\forms\support;

use app\classes\enum\DepartmentEnum;
use app\classes\enum\TicketStatusEnum;
use app\classes\Form;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\EnumValidator;
use app\classes\validators\TicketIdValidator;

class TicketForm extends Form
{
    public $id;
    public $client_account_id;
    public $user_id;
    public $department;
    public $subject;
    public $description;
    public $status;

    public function rules()
    {
        return [
            [['id'], TicketIdValidator::className()],
            [['client_account_id'], AccountIdValidator::className()],
            [['user_id'], 'string', 'length' => 24],
            [['department'], EnumValidator::className(), 'enum' => DepartmentEnum::className()],
            [['subject'], 'string', 'max' => 1000],
            [['description'], 'string'],
            [['status'], EnumValidator::className(), 'enum' => TicketStatusEnum::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'department' => 'Отдел',
            'subject' => 'Название',
            'description' => 'Описание',
            'status' => 'Статус',
        ];
    }
}