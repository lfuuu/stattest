<?php

namespace app\classes\operators;

use app\classes\Singleton;
use app\classes\Assert;

class OperatorsFactory extends Singleton
{

    private static function getOperatorsList()
    {
        return [
            'onlime' => OperatorOnlime::class,
            'id36001' => OperatorOnlimeDevices::class,
            'id36259' => OperatorOnlimeStb::class,
        ];
    }

    public function getOperator($operator)
    {
        $operators = self::getOperatorsList();

        if (isset($operators[$operator])) {
            return new $operators[$operator];
        }

        Assert::isUnreachable('Operator not found');
    }

}