<?php
namespace app\exceptions;

use yii\base\Model;
use yii\web\HttpException;

class ModelValidationException extends HttpException
{
    private $_errors = [];

    /**
     * @param Model $model
     * @param int $errorCode код ошибки для API
     * @param int $statusCode http-код для браузера
     */
    public function __construct(Model $model, $errorCode = 0, $statusCode = 400)
    {
        $this->_errors = $model->getErrors();
        parent::__construct($statusCode, implode('. ', $model->getFirstErrors()), $errorCode);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}