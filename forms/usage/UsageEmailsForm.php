<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageEmails;

class UsageEmailsForm extends Form
{

    protected static $formModel = UsageEmails::class;

    public
        $actual_from,
        $actual_to,
        $local_part,
        $domain,
        $password,
        $client,
        $box_size,
        $box_quota,
        $enabled,
        $spam_act,
        $smtp_auth,
        $status;

    public function rules()
    {
        return [
            [['client'], 'required'],
            [
                [
                    'actual_from', 'actual_to', 'client', 'local_part', 'domain',
                    'password', 'ip_nat', 'ip_cidr', 'ip_gw', 'admin_login', 'admin_pass',
                    'numbers', 'logins', 'node', 'service', ''
                ], 'string'
            ],
            ['spam_act', 'in', 'range' => ['pass', 'mark', 'discard']],
            ['status', 'in', 'range' => ['connecting', 'working']],
            [['box_size', 'box_quota', 'enabled', 'smtp_auth',], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'local_part' => 'Почтовый ящик',
            'password' => 'Пароль',
            'box_quota' => 'Размер ящика',
            'box_size' => 'Занято, Kb',
            'status' => 'Состояние',
        ];
    }

}