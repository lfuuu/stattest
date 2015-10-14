<?php

namespace app\classes\operators;

use Yii;
use app\forms\external_operators\RequestOnlimeStbForm;

class OperatorOnlimeStb extends OperatorOnlimeDevices
{

    const OPERATOR = 'onlime-stb';
    const OPERATOR_CLIENT = 'id36130';

    protected static $requestProducts = [
        [
            'id' => 17609,
            'name' => 'SML-482 HD Base с Wi-Fi',
            'nameFull' => 'Приставка, SML-482 HD Base с опцией Wi-Fi',
            'id_1c' => 'd78e0644-6dbc-11e5-9421-00155d881200',
        ],
        [
            'id' => 13619,
            'name' => 'Доставка OnlimeTelecard внутри МКАД',
            'nameFull' => 'Услуга по доставке OnlimeTelecard по Москве в пределах МКАД',
            'id_1c' => '81d52242-4d6c-11e1-8572-00155d881200',
        ],
        [
            'id' => 13621,
            'name' => 'Доставка OnlimeTelecard за МКАД',
            'nameFull' => 'Услуга по доставке OnlimeTelecard по Москве за пределами МКАД',
            'id_1c' => '81d52245-4d6c-11e1-8572-00155d881200',
        ],
    ];

    public $isRollback = false;

    public function getRequestForm()
    {
        return new RequestOnlimeStbForm;
    }

}