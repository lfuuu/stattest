<?php
namespace app\classes;

use Yii;
use yii\base\ErrorException;

class ErrorHandler extends \yii\web\ErrorHandler
{
    public function handleError($code, $message, $file, $line)
    {
        if (in_array($code, [E_WARNING, E_NOTICE, E_STRICT])) {

            if (!class_exists('yii\\base\\ErrorException', false)) {
                require_once(YII2_PATH . '/base/ErrorException.php');
            }

            $exception = new ErrorException($message, $code, $code, $file, $line);

            $category = get_class($exception) . ':' . $exception->getSeverity();
            Yii::warning('Warning: ' . (string) $exception, $category);

            return true;
        }

        return parent::handleError($code, $message, $file, $line);
    }
}
