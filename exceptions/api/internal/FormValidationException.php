<?php
namespace app\exceptions\api\internal;

use yii\base\Model;
use app\exceptions\web\BadRequestHttpException;

class FormValidationException extends \app\exceptions\FormValidationException
{

    const EXCEPTION_PATH = '\\app\\exceptions\\api\\internal\\';
    const EXCEPTION_POSTFIX = 'ValidationException';

    private $exceptions = [
        'AccountId' => ['client_id', 'account_id', 'client_account_id'],
        'UsageVoip' => ['number']
    ];

    public function __construct(Model $form)
    {
        $errorKey = reset(array_keys($form->getFirstErrors()));
        $exceptionName = null;

        foreach ($this->exceptions as $exceptionKey => $fields) {
            if (in_array($errorKey, $fields, true)) {
                $exceptionName = self::EXCEPTION_PATH . $exceptionKey . self::EXCEPTION_POSTFIX;
                break;
            }
        }

        if (!class_exists($exceptionName)) {
            throw new BadRequestHttpException($form->getFirstError($errorKey));
        }

        throw new $exceptionName;
    }

}