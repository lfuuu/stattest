<?php
namespace app\exceptions\api\internal;

use yii\base\Model;
use app\exceptions\web\BadRequestHttpException;

class ExceptionValidationForm extends \app\exceptions\FormValidationException
{

    const EXCEPTION_PREFIX = 'Validation';

    private $exceptions = [
        'AccountId' => ['client_id', 'account_id', 'client_account_id'],
        'UsageVoip' => ['number']
    ];

    public function __construct(Model $form)
    {
        $errorKey = reset(array_keys($form->getFirstErrors()));

        foreach ($this->exceptions as $exceptionKey => $fields) {
            if (in_array($errorKey, $fields, true)) {
                throw ExceptionFactory::get(self::EXCEPTION_PREFIX . $exceptionKey);
            }
        }

        throw new BadRequestHttpException($form->getFirstError($errorKey));
    }

}