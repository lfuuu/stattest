<?php
namespace app\exceptions\api\internal;

use app\classes\Assert;

abstract class ExceptionFactory
{

    /**
     * @return array
     */
    private static function exceptionsList()
    {
        return [
            'ValidationAccountId' => new ExceptionValidationAccountId,
            'ValidationUsageVoip' => new ExceptionValidationUsageVoip,
        ];
    }

    /**
     * @param string $exceptionKey
     * @return \Exception
     * @throws \yii\base\Exception
     */
    public static function get($exceptionKey)
    {
        if (!array_key_exists($exceptionKey, self::exceptionsList())) {
            Assert::isUnreachable('Exception not found');
        }

        return self::exceptionsList()[$exceptionKey];
    }

}