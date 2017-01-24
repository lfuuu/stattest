<?php
namespace app\classes;

use app\exceptions\ModelValidationException;
use Yii;
use yii\base\ErrorException;

class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * @param int $code
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public function handleError($code, $message, $file, $line)
    {
        if (/*!YII_DEBUG && */
        in_array($code, [E_WARNING, E_NOTICE, E_STRICT])
        ) {

            if (!class_exists('yii\\base\\ErrorException', false)) {
                require_once YII2_PATH . '/base/ErrorException.php';
            }

            $exception = new ErrorException($message, $code, $code, $file, $line);

            $category = get_class($exception) . ':' . $exception->getSeverity();
            Yii::warning('Warning: ' . (string)$exception, $category);

            return true;
        }

        return parent::handleError($code, $message, $file, $line);
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function convertExceptionToArray($exception)
    {
        $array = parent::convertExceptionToArray($exception);

        if ($exception instanceof ModelValidationException) {
            $array['errors'] = $exception->getErrors();
        }

        return $array;
    }
}
