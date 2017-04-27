<?php

namespace app\modules\notifier\components\traits;

use ErrorException;
use Yii;

trait FormExceptionTrait
{

    /**
     * @param \Exception $exception
     * @return bool
     */
    public function catchException(\Exception $exception)
    {
        if ($exception instanceof ErrorException) {
            Yii::$app->session->setFlash(
                'error',
                'Ошибка работы с MAILER. Текст ошибки:' . $exception->getMessage()
            );
        } else {
            Yii::$app->session->setFlash(
                'error',
                'Отсутствует соединение с MAILER' . PHP_EOL . '<br />' . '<pre class="text-left">' . $exception->getMessage() . '</pre>'
            );
        }

        return false;
    }

}