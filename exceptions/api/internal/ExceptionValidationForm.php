<?php
namespace app\exceptions\api\internal;

use yii\base\Model;
use app\exceptions\web\BadRequestHttpException;

class ExceptionValidationForm extends \app\exceptions\ModelValidationException
{

    const EXCEPTION_PREFIX = 'Validation';

    private $exceptions = [
        'AccountId' => ['client_id', 'account_id', 'client_account_id'],
        'UsageVoip' => ['number']
    ];

    public function __construct(Model $model)
    {
        $errorKey = reset(array_keys($model->getFirstErrors()));

        foreach ($this->exceptions as $exceptionKey => $fields) {
            if (in_array($errorKey, $fields, true)) {
                throw ExceptionFactory::get(self::EXCEPTION_PREFIX . $exceptionKey);
            }
        }

        throw new BadRequestHttpException($model->getFirstError($errorKey));
    }

}