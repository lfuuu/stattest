<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\UsageEmails;
use yii\db\Expression;

class UsageEmailsForm extends Form
{

    protected static $formModel = UsageEmails::class;

    public
        $id,
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
            [['client', 'local_part'], 'required'],
            [
                [
                    'actual_from', 'actual_to', 'client', 'local_part', 'domain', 'password',
                ], 'string'
            ],
            ['spam_act', 'in', 'range' => ['pass', 'mark', 'discard']],
            ['status', 'in', 'range' => ['connecting', 'working']],
            [['box_size', 'box_quota', 'enabled', 'smtp_auth',], 'integer'],
            [['box_size', 'smtp_auth'], 'default', 'value' => 0],
            ['box_quota', 'default', 'value' => 50000],
            ['enabled', 'default', 'value' => 1],
            ['spam_act', 'default', 'value' => 'pass'],
            ['local_part', 'validateLocalPart'],
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

    public function validateLocalPart()
    {
        if(!is_null(
            UsageEmails::find()
                ->where(new Expression('id IS NOT NULL'))
                ->andWhere(['<=', 'actual_from', new Expression('CAST("' . $this->actual_from . '" AS DATE)')])
                ->andWhere(['>', 'actual_to', new Expression('CAST("' . $this->actual_from . '" AS DATE)')])
                ->andWhere(['domain' => $this->domain])
                ->andWhere(['local_part' => $this->local_part])
                ->one()
        )) {
            $this->addError('local_part', 'Такой адрес уже занят');
        }
    }

}