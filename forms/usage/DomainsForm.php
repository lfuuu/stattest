<?php
namespace app\forms\usage;

use Yii;
use app\classes\Form;
use app\models\Domain;

class DomainsForm extends Form
{

    protected static $formModel = Domain::class;

    public
        $actual_from,
        $actual_to,
        $domain,
        $client,
        $primary_mx,
        $registrator,
        $rucenter_form_no,
        $dns,
        $paid_till;

    public function rules()
    {
        return [
            [['client', 'domain', 'primary_mx', 'paid_till'], 'required'],
            [['actual_from', 'actual_to', 'client', 'primary_mx', 'domain', 'dns', 'paid_till',], 'string'],
            ['registrator', 'in', 'range' => ['', 'RUCENTER-REG-RIPN']],
            [['rucenter_form_no',], 'number'],
            [['rucenter_form_no',], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'domain' => 'Домен',
            'primary_mx' => 'Primary MX',
            'registrator' => 'Регистратор',
            'dns' => 'DNS',
            'paid_till' => 'Оплачен до',
            'rucenter_form_no' => 'Номер клиента в RuCenter',
        ];
    }

}