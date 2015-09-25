<?php

namespace app\classes\operators;

use app\classes\Singleton;
use app\classes\Assert;

class OperatorsFactory extends Singleton
{

    private static function getOperatorsList()
    {
        return [
            'onlime' => OperatorOnlime::className(),
            //'id36001' => OperatorOnlimeDevices::className(),
            'id35559' => OperatorOnlimeDevices::className(),
        ];
    }

    public function getOperator($operator)
    {
        $operators = self::getOperatorsList();

        if (isset($operators[ $operator ]))
            return new $operators[ $operator ];

        Assert::isUnreachable('Operator not found');
    }

}